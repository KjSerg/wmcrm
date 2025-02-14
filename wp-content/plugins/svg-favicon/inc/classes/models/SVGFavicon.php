<?php

namespace FOF\SVGFAVICON\Models;

class SVGFavicon
{
    public string $name = 'SVGFavicon';
    public string $svg_favicon;
    public string $background_color = '#000000';
    public string $theme_color = '#FFFFFF';
    public int $manifest = 0;
    public string $mask_icon;
    public string $mask_color = '#000000';
    public string $apple_touch_icon;
    public string $date;
}