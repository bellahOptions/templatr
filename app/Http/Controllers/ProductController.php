<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Services\Download\DownloadSecurityManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductController extends Controller
{
    protected DownloadSecurityManager $downloadSecurity;

    public function __construct(DownloadSecurityManager $downloadSecurity)
    {
        $this->downloadSecurity = $downloadSecurity;
    }

    public function index()
    {
        return view('products.index');
    }

    public function show(Product $product)
    {
        if (! $product->is_published) {
            abort(404);
        }

        $product->increment('view_count');
        $relatedProducts = Product::published()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
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
                    'max_downloads' => 2,
                ];
            }
        }

        return view('products.show', compact(
            'product', 'relatedProducts', 'reviews', 'userReview',
            'isInWishlist', 'hasPurchased', 'downloadInfo'
        ));
    }

    /**
     * Secure download method with multi-layer security checks.
     *
     * Security layers:
     * 1. IP rate limiting (10 attempts per 15 minutes)
     * 2. Product existence and published status
     * 3. File existence on storage
     * 4. Authentication check
     * 5. Purchase verification (order + payment_status)
     * 6. Payment gateway double-verification (re-verify with Paystack/Flutterwave/Interswitch)
     * 7. Download limit enforcement (1 for guests, 2 for authenticated users)
     * 8. Token expiration check
     * 9. CSRF protection for authenticated users
     */
    public function download(Product $product)
    {
        try {
            // Run through all security layers
            $authorization = $this->downloadSecurity->authorizeDownload($product);

            $filePath = $product->file_path;
            $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'zip';
            $fileName = $product->slug.'.'.$ext;

            // Record the download with audit trail
            $this->downloadSecurity->recordDownload($authorization);

            Log::info("Download authorized: product #{$product->id} by ".
                (Auth::check() ? 'user #'.Auth::id() : 'guest (token-based)'));

            if (! Storage::disk('public')->exists($filePath)) {
                Log::error("Download failed: File missing at {$filePath} for product #{$product->id}");

                return back()->with('error', 'The file could not be found on the server. Please contact support.');
            }

            return response()->download(
                Storage::disk('public')->path($filePath),
                $fileName,
                [
                    'Content-Type' => 'application/octet-stream',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                    'X-Content-Type-Options' => 'nosniff',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                ]
            );

        } catch (HttpException $e) {
            // Handle security exceptions with user-friendly messages
            $message = match ($e->getStatusCode()) {
                429 => 'Too many download attempts. Please wait 15 minutes before trying again.',
                401 => 'Authentication required. Please sign in to download this item.',
                403 => $e->getMessage() ?: 'You are not authorized to download this item.',
                404 => 'This product could not be found.',
                410 => 'Your download link has expired. Please contact support for assistance.',
                default => 'An error occurred while processing your download request.',
            };

            return back()->with('error', $message);
        } catch (\Exception $e) {
            Log::error('Download exception: '.$e->getMessage(), [
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
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
}
