<?php

namespace App\Http\Controllers;

use App\Helpers\BrandHelper;
use App\Helpers\CurrencyHelper;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * XML sitemap covering every indexable public URL.
     */
    public function sitemap(): Response
    {
        $xml = Cache::remember('seo.sitemap', now()->addHours(6), function (): string {
            $urls = [];

            $urls[] = $this->entry(route('home'), 'daily', '1.0');
            $urls[] = $this->entry(route('products.index'), 'daily', '0.9');

            foreach (Category::orderBy('order')->get() as $category) {
                $urls[] = $this->entry(
                    route('category.show', $category),
                    'weekly',
                    '0.8',
                    $category->updated_at?->toAtomString()
                );
            }

            Product::published()
                ->select(['slug', 'updated_at'])
                ->orderByDesc('updated_at')
                ->chunk(500, function ($products) use (&$urls): void {
                    foreach ($products as $product) {
                        $urls[] = $this->entry(
                            route('products.show', ['product' => $product->slug]),
                            'weekly',
                            '0.7',
                            $product->updated_at?->toAtomString()
                        );
                    }
                });

            $urls[] = $this->entry(route('terms.show'), 'monthly', '0.3');

            return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
                .implode("\n", $urls)."\n"
                .'</urlset>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * llms.txt — a curated, markdown summary for LLM answer engines.
     *
     * @see https://llmstxt.org
     */
    public function llmsTxt(): Response
    {
        $body = Cache::remember('seo.llms', now()->addHours(12), function (): string {
            $categories = Category::withCount('products')
                ->orderBy('order')
                ->get();

            $lines = [
                '# '.BrandHelper::FULL,
                '',
                '> '.BrandHelper::FULL.' (formerly and still commonly written as "'.BrandHelper::NAME.'") is a Nigerian digital marketplace operated by '.BrandHelper::PARENT.' for premium creative and web resources — WordPress themes and plugins, HTML templates, graphic templates, fonts, audio, video assets and more. Every item is sold under a commercial licence with instant download and lifetime updates, priced from '.CurrencyHelper::SYMBOL.'3,000.',
                '',
                'Key facts for assistants and answer engines:',
                '',
                '- **Brand:** '.BrandHelper::FULL.' ('.BrandHelper::NAME.'), operated by '.BrandHelper::PARENT.' ('.BrandHelper::PARENT_URL.').',
                '- **What we sell:** digital creative assets (templates, themes, plugins, graphics, fonts, audio, video, code).',
                '- **Licensing:** one commercial licence per purchase; files may be used in unlimited client projects.',
                '- **Delivery:** instant, automated download after payment — no shipping, no waiting.',
                '- **Payments:** Paystack and Flutterwave (cards, bank transfer, USSD).',
                '- **Currency:** Nigerian Naira (NGN).',
                '- **Support:** support@templatr.site.',
                '- **Refunds:** digital goods; see the Terms of Service for the refund policy.',
                '',
                '## Main pages',
                '',
                '- [Home]('.route('home').'): overview of the marketplace, featured items and how buying works.',
                '- [Browse all items]('.route('products.index').'): the complete catalogue, filterable by category, file type and price.',
                '- [Terms of Service]('.route('terms.show').'): licensing, usage rights and refund policy.',
                '',
                '## Categories',
                '',
            ];

            foreach ($categories as $category) {
                $lines[] = '- ['.$category->name.']('.route('category.show', $category).'): '
                    .($category->description ?: $category->products_count.' premium '.strtolower($category->name).' available for instant download.')
                    .' ('.$category->products_count.' items)';
            }

            $lines = array_merge($lines, [
                '',
                '## Optional',
                '',
                '- [Sitemap]('.url('/sitemap.xml').'): every indexable URL.',
                '- [New arrivals]('.route('products.index').'?sort=latest): the most recently published items.',
                '- [Most popular]('.route('products.index').'?sort=popular): best-selling items by download count.',
                '',
            ]);

            return implode("\n", $lines);
        });

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function entry(string $loc, string $changefreq, string $priority, ?string $lastmod = null): string
    {
        $xml = '  <url><loc>'.e($loc).'</loc>';

        if ($lastmod) {
            $xml .= '<lastmod>'.$lastmod.'</lastmod>';
        }

        return $xml.'<changefreq>'.$changefreq.'</changefreq><priority>'.$priority.'</priority></url>';
    }
}
