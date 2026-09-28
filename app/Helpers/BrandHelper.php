<?php

namespace App\Helpers;

/**
 * Single source of truth for how the brand is named in titles, meta tags and schema.
 *
 * "Templatr" alone is a made-up word with no search entity behind it, so public copy
 * always pairs it with the operating company, Bellah Options.
 */
final class BrandHelper
{
    public const NAME = 'Templatr';

    public const PARENT = 'Bellah Options';

    public const FULL = 'Templatr by Bellah Options';

    public const PARENT_URL = 'https://www.bellahoptions.com';

    public const TAGLINE = 'Premium Creative & Web Resources';

    public const DEFAULT_TITLE = self::FULL.' — '.self::TAGLINE;

    public const DEFAULT_DESCRIPTION = 'Templatr by Bellah Options is a marketplace for premium design templates, WordPress themes, plugins, graphics, fonts, audio and video assets. Instant download with a commercial licence.';
}
