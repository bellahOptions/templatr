<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    private const MAX_ROWS = 500;

    private const ALLOWED_EXTENSIONS = ['zip', 'rar', 'tar', 'gz', 'psd', 'ai', 'svg', 'mp3', 'wav', 'mp4', 'ttf', 'otf'];

    private const ALLOWED_FILE_TYPES = ['graphic', 'template', 'audio', 'video', 'font', 'plugin', '3d'];

    private const STAGING_DISK = 'local';

    private const STAGING_PATH = 'products/bulk';

    public function create(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $authors = User::authors()->orderBy('name')->get();

        $stagingFiles = collect(Storage::disk(self::STAGING_DISK)->files(self::STAGING_PATH))
            ->map(fn ($path) => basename($path))
            ->filter(fn ($name) => in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS))
            ->values();

        $importResults = $request->session()->pull('import_results');

        return view('admin.products.import', compact('categories', 'authors', 'stagingFiles', 'importResults'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
            'default_author_id' => 'nullable|exists:users,id',
            'default_category_id' => 'nullable|exists:categories,id',
        ]);

        $file = $request->file('csv_file');

        // Verify actual file MIME beyond extension check
        $realMime = $file->getMimeType();
        if (! in_array($realMime, ['text/plain', 'text/csv', 'application/csv']) && ! str_contains($realMime, 'text')) {
            return back()->withErrors(['csv_file' => 'Invalid file type. Please upload a plain CSV file.']);
        }

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->withErrors(['csv_file' => 'Could not read the uploaded file.']);
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);

            return back()->withErrors(['csv_file' => 'The CSV file is empty or unreadable.']);
        }
        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        $missing = array_diff(['title', 'price', 'file_type'], $headers);
        if (! empty($missing)) {
            fclose($handle);

            return back()->withErrors(['csv_file' => 'Missing required columns: '.implode(', ', $missing).'.']);
        }

        $colIndex = array_flip($headers);

        // Pre-load lookups to avoid N+1 queries during row processing
        $categories = Category::all()->keyBy(fn ($c) => strtolower(trim($c->name)));
        $authors = User::authors()->get()->keyBy(fn ($u) => strtolower(trim($u->email)));
        $existingTitles = Product::pluck('title')->map('strtolower')->flip()->toArray();

        $defaultAuthor = $request->filled('default_author_id') ? User::find($request->default_author_id) : null;
        $defaultCategory = $request->filled('default_category_id') ? Category::find($request->default_category_id) : null;

        $results = ['created' => 0, 'skipped' => 0, 'failed' => 0, 'rows' => []];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($rowNumber > self::MAX_ROWS + 1) {
                $results['rows'][] = $this->rowResult($rowNumber, 'failed', '—', 'Row limit of '.self::MAX_ROWS.' exceeded. Import stopped.');
                $results['failed']++;
                break;
            }

            $data = [];
            foreach ($colIndex as $col => $idx) {
                $data[$col] = isset($row[$idx]) ? trim($row[$idx]) : '';
            }

            // --- Title ---
            $title = $this->sanitizeString($data['title'] ?? '');
            if (empty($title)) {
                $results['rows'][] = $this->rowResult($rowNumber, 'failed', '(empty)', 'Title is required.');
                $results['failed']++;

                continue;
            }

            // --- Duplicate detection (DB + within-import) ---
            if (array_key_exists(strtolower($title), $existingTitles)) {
                $results['rows'][] = $this->rowResult($rowNumber, 'skipped', $title, 'Product with this title already exists.');
                $results['skipped']++;

                continue;
            }

            // --- Category ---
            $categoryKey = strtolower($this->sanitizeString($data['category'] ?? ''));
            $category = $categoryKey !== '' ? ($categories[$categoryKey] ?? null) : $defaultCategory;
            if (! $category) {
                $results['rows'][] = $this->rowResult($rowNumber, 'failed', $title, 'Category "'.($data['category'] ?? '').'" not found.');
                $results['failed']++;

                continue;
            }

            // --- Author ---
            $authorKey = strtolower($data['author_email'] ?? '');
            $author = $authorKey !== '' ? ($authors[$authorKey] ?? null) : $defaultAuthor;
            if (! $author) {
                $results['rows'][] = $this->rowResult($rowNumber, 'failed', $title, 'Author email "'.($data['author_email'] ?? '').'" not found.');
                $results['failed']++;

                continue;
            }

            // --- File type ---
            $fileType = strtolower(trim($data['file_type'] ?? ''));
            if (! in_array($fileType, self::ALLOWED_FILE_TYPES)) {
                $results['rows'][] = $this->rowResult($rowNumber, 'failed', $title, 'Invalid file_type "'.$fileType.'". Allowed: '.implode(', ', self::ALLOWED_FILE_TYPES).'.');
                $results['failed']++;

                continue;
            }

            // --- Price ---
            $price = filter_var($data['price'] ?? '', FILTER_VALIDATE_INT);
            if ($price === false || $price < 1) {
                $results['rows'][] = $this->rowResult($rowNumber, 'failed', $title, 'Price must be a positive integer.');
                $results['failed']++;

                continue;
            }

            // --- Sale price ---
            $salePrice = null;
            if (! empty($data['sale_price'] ?? '')) {
                $salePrice = filter_var($data['sale_price'], FILTER_VALIDATE_INT);
                if ($salePrice === false || $salePrice < 1 || $salePrice >= $price) {
                    $results['rows'][] = $this->rowResult($rowNumber, 'failed', $title, 'sale_price must be a positive integer less than price.');
                    $results['failed']++;

                    continue;
                }
            }

            $productData = [
                'category_id' => $category->id,
                'user_id' => $author->id,
                'title' => $title,
                'slug' => Str::slug($title).'-'.Str::random(5),
                'description' => $this->sanitizeString($data['description'] ?? '', 65535),
                'price' => $price,
                'sale_price' => $salePrice,
                'file_type' => $fileType,
                'thumbnail' => $this->sanitizeUrl($data['thumbnail'] ?? ''),
                'preview_image' => $this->sanitizeUrl($data['preview_image'] ?? ''),
                'tags' => $this->parseTags($data['tags'] ?? ''),
                'version' => $this->sanitizeString($data['version'] ?? '', 50) ?: null,
                'demo_url' => $this->sanitizeUrl($data['demo_url'] ?? ''),
                'requirements' => $this->sanitizeString($data['requirements'] ?? '', 65535) ?: null,
                'features' => $this->parseFeatures($data['features'] ?? ''),
                'is_featured' => $this->parseBool($data['is_featured'] ?? '0'),
                'is_published' => $this->parseBool($data['is_published'] ?? '1'),
            ];

            // --- Staged product file ---
            $stagingFileName = $data['file_name'] ?? '';
            if (! empty($stagingFileName)) {
                $stagingFileName = basename($stagingFileName); // prevent path traversal
                $ext = strtolower(pathinfo($stagingFileName, PATHINFO_EXTENSION));

                if (! in_array($ext, self::ALLOWED_EXTENSIONS)) {
                    $results['rows'][] = $this->rowResult($rowNumber, 'failed', $title, 'File "'.$stagingFileName.'" has a disallowed extension.');
                    $results['failed']++;

                    continue;
                }

                $stagingPath = self::STAGING_PATH.'/'.$stagingFileName;
                if (! Storage::disk(self::STAGING_DISK)->exists($stagingPath)) {
                    $results['rows'][] = $this->rowResult($rowNumber, 'failed', $title, 'File "'.$stagingFileName.'" not found in staging folder.');
                    $results['failed']++;

                    continue;
                }

                $sanitizedName = Str::slug(pathinfo($stagingFileName, PATHINFO_FILENAME)).'-'.Str::random(6).'.'.$ext;
                $finalPath = 'products/files/'.$sanitizedName;

                // Copy (not move) so staging file remains available for re-imports
                Storage::disk('public')->put($finalPath, Storage::disk(self::STAGING_DISK)->get($stagingPath));

                $fileSize = ! empty($data['file_size'])
                    ? (float) $data['file_size']
                    : round(Storage::disk(self::STAGING_DISK)->size($stagingPath) / 1048576, 2);

                $productData['file_path'] = $finalPath;
                $productData['original_file_name'] = $stagingFileName;
                $productData['file_size'] = $fileSize;
            } elseif (! empty($data['file_size'] ?? '')) {
                $productData['file_size'] = (float) $data['file_size'];
            }

            Product::create($productData);

            // Track new title so duplicates within the same CSV are caught
            $existingTitles[strtolower($title)] = true;

            $results['rows'][] = $this->rowResult($rowNumber, 'created', $title, '');
            $results['created']++;
        }

        fclose($handle);

        return redirect()->route('admin.products.import')->with('import_results', $results);
    }

    public function template(): Response
    {
        $columns = ['title', 'category', 'author_email', 'price', 'sale_price', 'file_type', 'file_name', 'file_size', 'thumbnail', 'preview_image', 'tags', 'version', 'demo_url', 'requirements', 'features', 'is_featured', 'is_published', 'description'];

        $example = [
            'Premium UI Kit', 'Templates', 'author@example.com', '2999', '',
            'graphic', 'ui-kit.zip', '',
            'https://res.cloudinary.com/example/image/upload/v1/thumb.jpg',
            'https://res.cloudinary.com/example/image/upload/v1/preview.jpg',
            'ui,kit,design', '1.0', 'https://demo.example.com',
            'Requires Figma 2023+', 'Dark mode|Light mode|Responsive',
            '0', '1', 'A professional UI kit with 200+ components.',
        ];

        $csv = $this->rowToCsv($columns)."\n".$this->rowToCsv($example)."\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="product-import-template.csv"',
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /** @return array{row: int, status: string, title: string, reason: string} */
    private function rowResult(int $row, string $status, string $title, string $reason): array
    {
        return compact('row', 'status', 'title', 'reason');
    }

    private function sanitizeString(string $value, int $maxLength = 255): string
    {
        return mb_substr(strip_tags(trim($value)), 0, $maxLength);
    }

    private function sanitizeUrl(string $value): ?string
    {
        $url = trim($value);
        if ($url === '') {
            return null;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }
        // Allow relative storage paths (no protocol, safe characters only)
        if (preg_match('/^[a-zA-Z0-9_\-\/\.]+$/', $url)) {
            return $url;
        }

        return null;
    }

    private function parseTags(string $value): ?string
    {
        if (trim($value) === '') {
            return null;
        }
        $tags = array_values(array_filter(
            array_map(fn ($t) => $this->sanitizeString(trim($t), 50), explode(',', $value))
        ));

        return $tags ? json_encode($tags) : null;
    }

    /** Features are pipe-separated in CSV: "Dark mode|Light mode|Responsive" */
    private function parseFeatures(string $value): ?array
    {
        if (trim($value) === '') {
            return null;
        }
        $features = array_values(array_filter(
            array_map(fn ($f) => $this->sanitizeString(trim($f), 255), explode('|', $value))
        ));

        return $features ?: null;
    }

    private function parseBool(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes']);
    }

    private function rowToCsv(array $fields): string
    {
        return implode(',', array_map(function ($cell) {
            return '"'.str_replace('"', '""', $cell).'"';
        }, $fields));
    }
}
