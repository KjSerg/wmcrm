<?php

namespace FOF\SVGFAVICON\Client;

use FOF\SVGFAVICON\Services\SiteIconsService;

class ClientSetup
{
    private SiteIconsService $siteIconsService;

    public function __construct(SiteIconsService $siteIconsService) {
        $this->siteIconsService = $siteIconsService;
    }

    public function svg_meta_tags(){

        if( has_site_icon() ){
            return;
        }

        $siteIcons = $this->siteIconsService->generateIcons();

        if( $siteIcons === false ){
            return;
        }

        $siteIcons = implode("\n", $siteIcons);

        echo $siteIcons."\n";
    }

    public function svg_favicon($meta_tags) {

        $siteIcons = $this->siteIconsService->generateIcons();

        if( $siteIcons === false ){
            return $meta_tags;
        }

        return $siteIcons;
    }
}