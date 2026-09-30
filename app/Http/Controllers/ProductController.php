<?php

namespace App\Http\Controllers;

use App\Http\Responses\WatermarkedFileResponse;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Services\Download\DownloadSecurityManager;
use App\Services\Download\WatermarkManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends Controller
{
    public function __construct(
        protected DownloadSecurityManager $downloadSecurity,
        protected WatermarkManager $watermarkManager,
    ) {}

    public function index()
    {
        return view('products.index');
    }

    public function show(Product $product): View
    {
        if (! $product->is_published) {
            abort(404);
        }

        $product->increment('view_count');
        $product->loadStats();

        $relatedProducts = Product::published()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->withStats()
            ->with(['category', 'author'])
            ->latest()
            ->take(4)
            ->get();

        $reviews = $product->reviews()->approved()->with('user')->latest()->get();
        $userReview = Auth::check() ? $product->reviews()->where('user_id', Auth::id())->first() : null;
        $isInWishlist = Auth::check() ? Auth::user()->wishlists()->where('product_id', $product->id)->exists() : false;
        $hasPurchased = Auth::check() ? Auth::user()->orders()->whereHas('items', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->where('payment_status', 'paid')->exists() : false;

        // Get download info for the current user
        $downloadInfo = null;
        if (Auth::check()) {
            $orderItem = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($q) {
                    $q->where('user_id', Auth::id())
                        ->where('payment_status', 'paid');
                })
                ->first();

            if ($orderItem) {
                $downloadInfo = [
                    'remaining_downloads' => $orderItem->remaining_downloads,
                    'download_count' => $orderItem->download_count,
                    'is_downloadable' => $orderItem->isDownloadable(),
                    'max_downloads' => OrderItem::MAX_DOWNLOADS,
                    'watermark' => $this->watermarkManager->label(),
                ];
            }
        }

        return view('products.show', compact(
            'product', 'relatedProducts', 'reviews', 'userReview',
            'isInWishlist', 'hasPurchased', 'downloadInfo'
        ));
    }

    /**
     * Secure download with a purchase notice stamped onto every delivered file.
     *
     * Security layers:
     * 1. Per-account concurrency guard and IP rate limiting
     * 2. Product existence and published status
     * 3. Authentication check
     * 4. Purchase verification (order + payment_status)
     * 5. Payment gateway re-verification, cached so repeat downloads make no HTTP call
     * 6. Download limit enforcement
     * 7. Token hashing and expiration check
     * 8. Delivery of a watermarked artifact, streamed with Range support
     */
    public function download(Request $request, Product $product): Response
    {
        $authorization = null;

        try {
            $authorization = $this->downloadSecurity->authorizeDownload($product);

            $filePath = $product->file_path;

            if (! $filePath || ! Storage::disk('public')->exists($filePath)) {
                Log::error('Download failed: file missing.', [
                    'product_id' => $product->id,
                    'file_path' => $filePath,
                ]);

                return back()->with('error', 'The file could not be found on the server. Please contact support.');
            }

            $result = $this->watermarkManager->prepare($product, $authorization->orderItem);

            if (! is_file($result->path)) {
                Log::error('Download failed: deliverable unavailable.', [
                    'product_id' => $product->id,
                    'watermarked' => $result->watermarked,
                ]);

                return back()->with('error', 'The file could not be found on the server. Please contact support.');
            }

            // Recorded only once a deliverable actually exists, so a failed
            // render never burns one of the buyer's download credits.
            $this->downloadSecurity->recordDownload($authorization);

            Log::info('Download authorized: product #'.$product->id.' by '.
                (Auth::check() ? 'user #'.Auth::id() : 'guest (token-based)'), [
                    'watermark_strategy' => $result->strategy,
                    'bytes' => $result->size(),
                ]);

            $response = $this->deliver($request, $result->path, $result->fileName);

            // The concurrency slot stays held until the client has received the
            // last byte, so it is released from the terminating callback rather
            // than here.
            $held = $authorization;
            app()->terminating(fn () => $this->downloadSecurity->releaseDownloadSlot($held));

            return $response;
        } catch (HttpException $e) {
            if ($authorization) {
                $this->downloadSecurity->releaseDownloadSlot($authorization);
            }

            $message = match ($e->getStatusCode()) {
                429 => $e->getMessage() ?: 'Too many download attempts. Please wait before trying again.',
                401 => 'Authentication required. Please sign in to download this item.',
                403 => $e->getMessage() ?: 'You are not authorized to download this item.',
                404 => 'This product could not be found.',
                410 => 'Your download link has expired. Please contact support for assistance.',
                default => 'An error occurred while processing your download request.',
            };

            return back()->with('error', $message);
        } catch (\Exception $e) {
            if ($authorization) {
                $this->downloadSecurity->releaseDownloadSlot($authorization);
            }

            Log::error('Download exception: '.$e->getMessage(), [
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);

            return back()->with('error', 'An unexpected error occurred. Please try again or contact support.');
        }
    }

    public function storeReview(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $hasPurchased = Auth::user()->orders()->whereHas('items', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })->where('payment_status', 'paid')->exists();

        if (! $hasPurchased) {
            return back()->with('error', 'You can only review items you have purchased.');
        }

        Review::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => Auth::id()],
            ['rating' => $validated['rating'], 'review' => $validated['review'], 'is_approved' => true]
        );

        return back()->with('success', 'Review submitted successfully!');
    }

    /**
     * Stream the deliverable, or hand it to the web server when the host
     * exposes an internal offload directive.
     */
    protected function deliver(Request $request, string $path, string $fileName): Response
    {
        $response = WatermarkedFileResponse::make($path, $fileName);
        $offload = $response->offloadHeaders();

        if ($offload !== null) {
            $binary = new BinaryFileResponse($path, 200, [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Disposition' => 'attachment; filename="'.addslashes($fileName).'"',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            ], false);

            $binary->headers->set('X-Sendfile-Type', 'X-Sendfile');
            $binary->headers->set($offload[0], $offload[1]);
            $binary->setContentDisposition('attachment', $fileName);

            return $binary;
        }

        return $response->prepare($request);
    }
}
