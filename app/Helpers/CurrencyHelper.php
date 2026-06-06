<?php

namespace App\Helpers;

class CurrencyHelper
{
    const SYMBOL = '₦';

    const CODE = 'NGN';

    // Default prices based on product type
    const WORDPRESS_THEME_PRICE = 3000;

    const PLUGIN_PRICE = 3000;

    const DESIGN_PACK_PRICE = 5000;

    /**
     * Format amount with Naira symbol
     */
    public static function format($amount)
    {
        if ($amount === 0 || $amount === null) {
            return self::SYMBOL.'0';
        }

        return self::SYMBOL.number_format((int) $amount, 0, '.', ',');
    }

    /**
     * Format amount without decimals
     */
    public static function formatInt($amount)
    {
        if ($amount === 0 || $amount === null) {
            return self::SYMBOL.'0';
        }

        return self::SYMBOL.number_format($amount, 0, '.', ',');
    }

    /**
     * Get default price for a product based on its type/category
     */
    public static function getDefaultPrice($productType, $categoryName = '')
    {
        $type = strtolower($productType);
        $category = strtolower($categoryName);

        // WordPress themes and plugins default to ₦3,000
        if (strpos($type, 'plugin') !== false || strpos($category, 'plugin') !== false) {
            return self::PLUGIN_PRICE;
        }

        // WordPress themes
        if (strpos($type, 'template') !== false &&
            (strpos($category, 'wordpress') !== false || strpos($category, 'theme') !== false)) {
            return self::WORDPRESS_THEME_PRICE;
        }

        // Design packs, templates (non-WordPress), graphics, UI kits, fonts, audio, video, 3D assets, print templates
        if (in_array($type, ['graphic', 'template', 'font', 'audio', 'video', '3d'])) {
            return self::DESIGN_PACK_PRICE;
        }

        // Default
        return self::DESIGN_PACK_PRICE;
    }
}
