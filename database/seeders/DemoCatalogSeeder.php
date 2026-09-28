<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Slideshow;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Populates a complete, presentable demo marketplace.
 *
 * Imagery is hot-linked from the Unsplash CDN (https://unsplash.com), which the Unsplash
 * licence permits. Products still point at a local placeholder archive so the secure
 * download flow can be exercised; replace them with real uploads before going live.
 */
class DemoCatalogSeeder extends Seeder
{
    private const IMAGE_SUFFIX = '?auto=format&fit=crop&w=1600&q=80';

    private const PREVIEW_SUFFIX = '?auto=format&fit=crop&w=1400&h=1050&q=80';

    /**
     * Verified Unsplash photo ids, grouped so imagery matches the category.
     *
     * @var array<string, list<string>>
     */
    private const IMAGES = [
        'wordpress-themes' => [
            'photo-1467232004584-a241de8bcf5d',
            'photo-1544197150-b99a580bb7a8',
            'photo-1522199755839-a2bacb67c546',
            'photo-1461749280684-dccba630e2f6',
        ],
        'html-templates' => [
            'photo-1498050108023-c5249f4df085',
            'photo-1517180102446-f3ece451e9d8',
            'photo-1555066931-4365d14bab8c',
            'photo-1531297484001-80022131f5a1',
        ],
        'graphics' => [
            'photo-1561070791-2526d30994b5',
            'photo-1558655146-9f40138edfeb',
            'photo-1541462608143-67571c6738dd',
            'photo-1493612276216-ee3925520721',
        ],
        'ui-kits' => [
            'photo-1607799279861-4dd421887fb3',
            'photo-1581291518857-4e27b48ff24e',
            'photo-1545235617-9465d2a55698',
            'photo-1550745165-9bc0b252726f',
        ],
        'fonts' => [
            'photo-1598488035139-bdbb2231ce04',
            'photo-1524661135-423995f22d0b',
            'photo-1550859492-d5da9d8e45f3',
            'photo-1502691876148-a84978e59af8',
        ],
        'audio' => [
            'photo-1509228468518-180dd4864904',
            'photo-1511671782779-c97d3d27a1d4',
            'photo-1478737270239-2f02b77fc618',
            'photo-1493225457124-a3eb161ffa5f',
        ],
        'video' => [
            'photo-1512941937669-90a1b58e7e9c',
            'photo-1574717024653-61fd2cf4d44d',
            'photo-1492619375914-88005aa9e8fb',
            'photo-1536440136628-849c177e76a1',
        ],
        'plugins' => [
            'photo-1555949963-aa79dcee981c',
            'photo-1587620962725-abab7fe55159',
            'photo-1517336714731-489689fd1ca8',
            'photo-1470225620780-dba8ba36b745',
        ],
        '3d-assets' => [
            'photo-1618005182384-a83a8bd57fbe',
            'photo-1626785774573-4b799315345d',
            'photo-1618556450991-2f1af64e8191',
            'photo-1620712943543-bcc4688e7485',
        ],
        'print-templates' => [
            'photo-1517842645767-c639042777db',
            'photo-1586953208448-b95a79798f07',
            'photo-1611532736597-de2d4265fba3',
            'photo-1586075010923-2dd4570fb338',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding demo authors…');
        $authors = $this->seedAuthors();

        $this->command->info('Seeding categories…');
        $categories = $this->seedCategories();

        $this->seedPlaceholderArchive();

        $this->command->info('Seeding products…');
        $products = $this->seedProducts($categories, $authors);

        $this->command->info('Seeding reviews…');
        $this->seedReviews($products);

        $this->command->info('Seeding hero slideshows…');
        $this->seedSlideshows();

        $this->command->info(sprintf(
            'Demo data ready — %d categories, %d authors, %d products, %d reviews.',
            Category::count(),
            count($authors),
            Product::count(),
            Review::count()
        ));
    }

    /**
     * @return list<User>
     */
    private function seedAuthors(): array
    {
        $people = [
            ['Aare Abefe', 'aare@templatr.site', 'Founder and product designer at Bellah Options.'],
            ['Ngozi Okafor', 'ngozi@templatr.site', 'WordPress developer shipping themes and plugins since 2016.'],
            ['Tunde Balogun', 'tunde@templatr.site', 'Brand and identity designer. Logos, mockups and print systems.'],
            ['Amaka Eze', 'amaka@templatr.site', 'Motion designer and sound engineer.'],
            ['Ibrahim Musa', 'ibrahim@templatr.site', 'Front-end engineer and UI kit maintainer.'],
            ['Chiamaka Nwosu', 'chiamaka@templatr.site', 'Type designer and typographer.'],
        ];

        $authors = [];

        foreach ($people as [$name, $email, $bio]) {
            $authors[] = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'author',
                    'bio' => $bio,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $authors;
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $definitions = [
            ['WordPress Themes', 'wordpress-themes', 'Premium WordPress themes for every need', 'wordpress', 1],
            ['HTML Templates', 'html-templates', 'Responsive HTML website templates', 'code', 2],
            ['Graphics', 'graphics', 'Stock photos, illustrations, and graphics', 'image', 3],
            ['UI Kits', 'ui-kits', 'User interface design kits and templates', 'palette', 4],
            ['Fonts', 'fonts', 'Professional fonts and typography', 'font', 5],
            ['Audio', 'audio', 'Music, sound effects and audio templates', 'music', 6],
            ['Video', 'video', 'Video templates, stock footage and motion graphics', 'video', 7],
            ['Plugins', 'plugins', 'WordPress plugins, extensions and add-ons', 'puzzle', 8],
            ['3D Assets', '3d-assets', '3D models, textures and renderings', 'cube', 9],
            ['Print Templates', 'print-templates', 'Flyers, brochures, business cards and more', 'file-text', 10],
        ];

        $categories = [];

        foreach ($definitions as [$name, $slug, $description, $icon, $order]) {
            $categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                compact('name', 'description', 'icon', 'order')
            );
        }

        return $categories;
    }

    /**
     * A single tiny archive every demo product points at, so the download route works.
     */
    private function seedPlaceholderArchive(): void
    {
        $path = 'demo/templatr-sample-asset.zip';

        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $zip = new \ZipArchive;
        $absolute = Storage::disk('public')->path($path);
        Storage::disk('public')->makeDirectory('demo');

        if ($zip->open($absolute, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $zip->addFromString('README.txt', "Templatr demo asset\n\nThis placeholder archive stands in for the real downloadable file in seeded demo data.\n");
            $zip->close();
        }
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  list<User>  $authors
     * @return list<Product>
     */
    private function seedProducts(array $categories, array $authors): array
    {
        $catalog = [
            'wordpress-themes' => [
                ['Aurora Multipurpose WordPress Theme', 18000, 12000, 2, ['wordpress', 'multipurpose', 'elementor', 'woocommerce']],
                ['Ledger Finance & Fintech Theme', 24000, null, 1, ['wordpress', 'fintech', 'finance', 'blocks']],
                ['Harvest Restaurant & Cafe Theme', 16500, 11000, 3, ['wordpress', 'restaurant', 'booking', 'menu']],
                ['Meridian Agency Portfolio Theme', 20000, 15000, 0, ['wordpress', 'agency', 'portfolio', 'gutenberg']],
            ],
            'html-templates' => [
                ['Nimbus SaaS Landing Page Kit', 12500, 8500, 4, ['html', 'saas', 'landing', 'tailwind']],
                ['Atlas Documentation Template', 15000, null, 2, ['html', 'docs', 'technical', 'responsive']],
                ['Ovation Event & Conference Template', 11000, 7500, 4, ['html', 'events', 'conference', 'bootstrap']],
                ['Pulse Admin Dashboard Template', 22000, 16500, 1, ['html', 'dashboard', 'admin', 'charts']],
            ],
            'graphics' => [
                ['Botanica Botanical Illustration Pack', 9000, null, 2, ['illustration', 'botanical', 'vector', 'png']],
                ['Concrete Textures & Surfaces Vol. 2', 12000, 8000, 3, ['textures', 'backgrounds', 'high-res', 'overlays']],
                ['Studio Mockup Collection', 16000, 11500, 2, ['mockup', 'branding', 'photoshop', 'smart-object']],
                ['Geometric Gradient Background Pack', 7500, 5000, 4, ['gradient', 'abstract', 'backgrounds', '4k']],
            ],
            'ui-kits' => [
                ['Nova Design System for Figma', 25000, 19000, 4, ['figma', 'design-system', 'components', 'variables']],
                ['Mobile Banking UI Kit', 21000, null, 1, ['ui-kit', 'fintech', 'mobile', 'figma']],
                ['SaaS Dashboard Component Library', 19500, 14000, 4, ['ui-kit', 'dashboard', 'components', 'dark-mode']],
                ['E-commerce Wireframe Kit', 8500, 6000, 0, ['wireframe', 'ecommerce', 'ux', 'sketch']],
            ],
            'fonts' => [
                ['Kariba Display Typeface', 14000, null, 5, ['font', 'display', 'headline', 'variable']],
                ['Grotesk Sans Family — 8 Weights', 18500, 14000, 5, ['font', 'sans-serif', 'family', 'webfont']],
                ['Ledger Mono Code Typeface', 12000, 9000, 5, ['font', 'monospace', 'code', 'ligatures']],
                ['Serif Noire Editorial Family', 15500, null, 5, ['font', 'serif', 'editorial', 'italic']],
            ],
            'audio' => [
                ['Afrobeat Corporate Background Tracks', 13000, 9500, 3, ['audio', 'afrobeat', 'corporate', 'royalty-free']],
                ['Cinematic Trailer Sound Design Kit', 17000, null, 3, ['audio', 'cinematic', 'trailer', 'sfx']],
                ['Podcast Intro & Outro Bundle', 8500, 6000, 3, ['audio', 'podcast', 'intro', 'stings']],
                ['Lo-fi Study Beats — 12 Tracks', 10500, 7500, 3, ['audio', 'lofi', 'beats', 'background']],
            ],
            'video' => [
                ['Animated Lower Thirds Pack', 14500, 10000, 3, ['video', 'after-effects', 'lower-thirds', 'broadcast']],
                ['Kinetic Typography Toolkit', 19000, 14500, 3, ['video', 'typography', 'animation', 'premiere']],
                ['Social Media Story Templates Vol. 3', 11500, null, 3, ['video', 'social', 'stories', 'vertical']],
                ['Logo Reveal Collection', 13500, 9500, 3, ['video', 'logo', 'reveal', 'intro']],
            ],
            'plugins' => [
                ['Templatr Checkout Booster for WooCommerce', 16000, 12000, 4, ['plugin', 'woocommerce', 'checkout', 'conversion']],
                ['SEO Schema Toolkit', 13500, null, 4, ['plugin', 'seo', 'schema', 'structured-data']],
                ['Advanced Booking Engine', 21500, 17000, 4, ['plugin', 'booking', 'calendar', 'payments']],
                ['Speed Cache Pro', 14500, 10500, 4, ['plugin', 'performance', 'cache', 'core-web-vitals']],
            ],
            '3d-assets' => [
                ['Product Render Studio Scenes', 22000, 17000, 3, ['3d', 'blender', 'product', 'render']],
                ['Low-Poly City Blocks Kit', 18000, null, 3, ['3d', 'low-poly', 'city', 'game-ready']],
                ['PBR Material Library Vol. 1', 16500, 12500, 3, ['3d', 'pbr', 'textures', 'materials']],
                ['Abstract Shape Pack for Motion', 12000, 9000, 3, ['3d', 'abstract', 'motion', 'c4d']],
            ],
            'print-templates' => [
                ['Executive Business Card Suite', 6500, 4500, 2, ['print', 'business-card', 'indesign', 'cmyk']],
                ['Tri-Fold Brochure System', 8500, 6000, 2, ['print', 'brochure', 'trifold', 'a4']],
                ['Event Flyer Collection Vol. 4', 7000, 5000, 2, ['print', 'flyer', 'event', 'a4']],
                ['Annual Report Layout Kit', 12500, null, 2, ['print', 'report', 'layout', 'a4']],
            ],
        ];

        $fileTypes = [
            'wordpress-themes' => 'template',
            'html-templates' => 'template',
            'graphics' => 'graphic',
            'ui-kits' => 'graphic',
            'fonts' => 'font',
            'audio' => 'audio',
            'video' => 'video',
            'plugins' => 'plugin',
            '3d-assets' => '3d',
            'print-templates' => 'graphic',
        ];

        $products = [];
        $featuredSlots = 0;

        foreach ($catalog as $categorySlug => $items) {
            $category = $categories[$categorySlug];
            $images = self::IMAGES[$categorySlug];
            $fileType = $fileTypes[$categorySlug];

            foreach ($items as $index => [$title, $price, $salePrice, $authorIndex, $tags]) {
                $slug = Str::slug($title);
                $author = $authors[$authorIndex % count($authors)];

                // Four hero products keep the homepage "Featured" row populated.
                $isFeatured = $featuredSlots < 8 && $index < 1;

                if ($isFeatured) {
                    $featuredSlots++;
                }

                $products[] = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'user_id' => $author->id,
                        'title' => $title,
                        'description' => $this->description($title, $category->name, $tags),
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'thumbnail' => 'https://images.unsplash.com/'.$images[$index % count($images)].self::IMAGE_SUFFIX,
                        'preview_image' => 'https://images.unsplash.com/'.$images[$index % count($images)].self::PREVIEW_SUFFIX,
                        'file_path' => 'demo/templatr-sample-asset.zip',
                        'original_file_name' => $slug.'.zip',
                        'file_type' => $fileType,
                        'file_size' => round(4 + (crc32($slug) % 1800) / 10, 2),
                        'download_count' => crc32($slug) % 480 + 12,
                        'view_count' => crc32($slug) % 5200 + 180,
                        'is_featured' => $isFeatured,
                        'is_published' => true,
                        'tags' => $tags,
                        'version' => '1.'.(crc32($slug) % 9).'.'.(crc32($slug) % 5),
                        'requirements' => $this->requirements($fileType),
                        'features' => $this->features($title, $fileType),
                        'compatible_browsers' => ['Chrome', 'Firefox', 'Safari', 'Edge'],
                        'includes' => $this->includes($fileType),
                        'columns' => $categorySlug === 'print-templates' ? ['2 columns', '3 columns'] : null,
                        'layouts' => $categorySlug === 'print-templates' ? ['A4', 'US Letter'] : null,
                    ]
                );
            }
        }

        return $products;
    }

    /**
     * @param  list<string>  $tags
     */
    private function description(string $title, string $categoryName, array $tags): string
    {
        $keywords = implode(', ', array_slice($tags, 0, 3));

        return <<<TEXT
        {$title} is a professionally crafted {$categoryName} asset built for teams that ship quickly.

        Every layer, artboard and file is named and organised, so you can drop it into an existing project or start from scratch without untangling someone else's mess. The package is fully documented and includes a quick-start guide plus free lifetime updates.

        Highlights: {$keywords}.
        TEXT;
    }

    private function requirements(string $fileType): string
    {
        return match ($fileType) {
            'template' => 'WordPress 6.4+ or any modern browser. PHP 8.1+ recommended for themes.',
            'plugin' => 'WordPress 6.4+ and PHP 8.1+. WooCommerce 8+ for commerce add-ons.',
            'font' => 'Any desktop or web environment. OTF, TTF, WOFF and WOFF2 included.',
            'audio' => 'Any DAW (Logic, Ableton, FL Studio, Reaper). WAV 24-bit and MP3 included.',
            'video' => 'After Effects CC 2022+, Premiere Pro CC 2022+ or DaVinci Resolve 18+.',
            '3d' => 'Blender 4.0+, Cinema 4D R25+ or any PBR-compatible renderer.',
            default => 'Adobe Photoshop CC 2021+, Illustrator CC 2021+ or any modern browser.',
        };
    }

    /**
     * @return list<string>
     */
    private function features(string $title, string $fileType): array
    {
        return [
            'Clean, fully layered and well-named source files',
            'Responsive and tested across modern devices',
            'Free lifetime updates and priority support',
            'Commercial licence included with every purchase',
            match ($fileType) {
                'template' => 'One-click demo import with sample content',
                'plugin' => 'Translation-ready with hooks for custom extensions',
                'font' => 'Full character set with kerning and OpenType alternates',
                'audio' => 'Pre-mastered WAV plus MP3 and sting versions',
                'video' => '4K and 1080p exports with editable project files',
                '3d' => 'Game-ready topology with baked PBR texture maps',
                default => 'Print-ready CMYK plus RGB versions at 300 DPI',
            },
        ];
    }

    /**
     * @return list<string>
     */
    private function includes(string $fileType): array
    {
        return match ($fileType) {
            'template' => ['Main template package', 'Documentation', 'Demo content', 'Child theme'],
            'plugin' => ['Installable plugin archive', 'Documentation', 'Sample configuration'],
            'font' => ['OTF', 'TTF', 'WOFF', 'WOFF2', 'Licence summary'],
            'audio' => ['WAV 24-bit masters', 'MP3 320kbps', 'Short stings'],
            'video' => ['Project files', '4K export', '1080p export', 'Sound effects'],
            '3d' => ['Source scene', 'FBX and OBJ exports', 'PBR texture maps'],
            default => ['Layered source file', 'Print-ready PDF', 'RGB exports', 'Font list'],
        };
    }

    /**
     * @param  list<Product>  $products
     */
    private function seedReviews(array $products): void
    {
        $customers = collect($this->customerNames())->map(fn (string $name, int $index) => User::firstOrCreate(
            ['email' => 'customer'.($index + 1).'@example.com'],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        ));

        $comments = [
            5 => [
                'Exactly what I needed. The files are immaculate and the documentation saved me hours.',
                'Best purchase I have made on a marketplace this year. Support replied within the hour.',
                'Dropped it straight into a client project and shipped the same week.',
            ],
            4 => [
                'Really solid quality. I only wish the dark variant was included by default.',
                'Great value for the price. A couple of small naming inconsistencies in the layers.',
                'Works exactly as advertised. Setup took a little longer than I expected.',
            ],
            3 => [
                'Decent overall, but I had to restructure a few things to fit my workflow.',
                'Good starting point. It needs some polish before client delivery.',
            ],
        ];

        foreach ($products as $product) {
            // Deterministic reviewer selection keeps re-seeding idempotent.
            $reviewers = $customers
                ->sortBy(fn (User $customer) => crc32($product->slug.$customer->id))
                ->take(2 + (crc32($product->slug) % 4));

            foreach ($reviewers as $reviewer) {
                $rating = match (crc32($product->slug.$reviewer->id) % 10) {
                    0, 1, 2, 3, 4, 5 => 5,
                    6, 7, 8 => 4,
                    default => 3,
                };

                $pool = $comments[$rating];

                Review::updateOrCreate(
                    ['product_id' => $product->id, 'user_id' => $reviewer->id],
                    [
                        'rating' => $rating,
                        'review' => $pool[crc32($product->slug.$reviewer->id) % count($pool)],
                        'is_approved' => true,
                    ]
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    private function customerNames(): array
    {
        return [
            'Emeka Obi', 'Fatima Bello', 'Segun Adeyemi', 'Halima Yusuf', 'Kelechi Nnamdi',
            'Zainab Abubakar', 'Oluwaseun Ajayi', 'Blessing Etim', 'Yakubu Danjuma', 'Adaora Ibe',
            'Musa Garba', 'Temitope Alade', 'Ifeoma Chukwu', 'Bashir Lawal',
        ];
    }

    private function seedSlideshows(): void
    {
        $slides = [
            ['Premium Assets, Nigerian Prices', 'Themes, plugins, graphics and fonts from ₦3,000 — with a commercial licence and instant download.', 'Browse the marketplace', '/products', 'photo-1522202176988-66273c2fd55f', 1],
            ['Everything Your Next Launch Needs', 'Hand-checked templates, UI kits and 3D assets from creators who actually ship.', 'Explore new arrivals', '/products?sort=latest', 'photo-1552664730-d307ca884978', 2],
            ['Built for Teams That Move Fast', 'Download once, use it in unlimited client projects. No subscriptions, no per-seat pricing.', 'See how it works', '/products', 'photo-1531482615713-2afd69097998', 3],
        ];

        foreach ($slides as [$title, $description, $ctaText, $ctaUrl, $image, $order]) {
            Slideshow::updateOrCreate(
                ['title' => $title],
                [
                    'description' => $description,
                    'cta_text' => $ctaText,
                    'cta_url' => $ctaUrl,
                    'image_url' => 'https://images.unsplash.com/'.$image.self::IMAGE_SUFFIX,
                    'sort_order' => $order,
                    'is_active' => true,
                ]
            );
        }
    }
}
