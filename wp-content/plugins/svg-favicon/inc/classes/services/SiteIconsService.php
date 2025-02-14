<?php

namespace FOF\SVGFAVICON\Services;

class SiteIconsService
{
    private OptionsService $options;

    public function __construct(OptionsService $options)
    {
        $this->options = $options;
    }

    public function generateIcons(){

        $options = $this->options->getOptions();

        if( empty($options['svg_favicon']) ){
            return false;
        }

        $tags = [];

        if( !empty($options['theme_color']) ){
            $tags[] = '<meta name="theme-color" content="'.$options['theme_color'].'" />';
        }

        $tags[] = '<link rel="icon" href="'.$options['svg_favicon'].'?svg-favicon='.$options['date'].'" size="any" type="image/x-icon" />';

        if( !empty($options['mask_icon']) ){
            $tags[] = '<link rel="mask-icon" href="'.$options['mask_icon'].'?svg-favicon='.$options['date'].'" color="'.( empty($options['mask_color']) ? '#000000' : $options['mask_color']).'" />';
        }

        if( !empty($options['apple_touch_icon'])) {
            $tags[] = '<link rel="apple-touch-icon" href="'.$options['apple_touch_icon'].'" />';
        }

        if( !empty($options['manifest']) && !empty($options['use_manifest']) ){
            $tags[] = '<link rel="manifest" href="'.$options['manifest'] .'?svg-favicon='.$options['date'].'" />';
        }

        return $tags;
    }
}