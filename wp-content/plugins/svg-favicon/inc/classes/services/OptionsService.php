<?php

namespace FOF\SVGFAVICON\Services;

use FOF\SVGFAVICON\Models\PluginInfo;
use FOF\SVGFAVICON\Models\SVGFavicon;
use FOF\SVGFAVICON\Tools\Utils;

class OptionsService
{
    private PluginInfo $pluginInfo;
    private Utils $utils;

    public function __construct()
    {
        $this->pluginInfo = new PluginInfo();
        $this->utils = new Utils();
    }

    public function getOptions()
    {
        $options = get_option( $this->pluginInfo->option );

        if( empty($options) ) {
            $options = (array)(new SVGFavicon());
        }

        $data = $this->utils->arrayMapRecursive('esc_attr', $options);

        $data['svg_favicon'] = empty($data['svg_favicon']) ? '' : sprintf("%s?date=%s", $data['svg_favicon'], $data['date']);
        $data['mask_icon'] = empty($data['mask_icon']) ? '' : sprintf("%s?date=%s", $data['mask_icon'], $data['date']);

        return $data;
    }
}