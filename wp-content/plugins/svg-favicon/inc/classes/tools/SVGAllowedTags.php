<?php

namespace FOF\SVGFAVICON\Tools;

use enshrined\svgSanitize\data\AllowedTags;

class SVGAllowedTags extends AllowedTags
{
    public static function getTags() {
        return parent::getTags(['style']);
    }
}