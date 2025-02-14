<?php

namespace FOF\SVGFAVICON\Models;

class PluginInfo
{
    public string $domain = 'svg-favicon';
    public string $query_arg = 'svgFaviconSec';
    public string $option = 'svg_favicon_plugin_options';
    public string $referrer_check = 'page=svg-favicon';
    public array $svg_map = [
        'svg_favicon' => 'svg-favicon',
        'mask_icon' => 'mask-icon',
    ];

    public array $settings = [
        'page_title' => 'SVG Favicon Settings',
        'menu_title' => 'SVG Favicon',
        'slug'       => 'svg-favicon-settings',
    ];

    public function ajaxUtils(): array
    {
        return [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'admin_url' => admin_url(),
            'nonce' => wp_create_nonce($this->domain),
            'domain' => $this->domain,
            'site_name' => get_bloginfo( 'name' ),
            'urls' => [
                'docs' => 'https://a415production.com/products/plugins/svg-plugin/svg-favicon-documentation/',
                'support' => 'https://a415production.com/products/support/forum/svg-favicon/',
                'plugin_page' => 'https://a415production.com/products/plugins/svg-plugin/',
                'rate_it' => 'https://wordpress.org/support/plugin/'.$this->svg_map['svg_favicon'].'/reviews/?filter=5',
                'donation' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DBMA4K32F6BAY&source=url',
            ],
            'version' => $this->version(),
        ];
    }

    private function getVersionNumber() {

        $transient = $this->domain.'_'.__FUNCTION__;

        if ( false === ( $tokens = get_transient( $transient ) ) ) {
            $tokens = token_get_all(file_get_contents(SVGFAVICON_PLUGIN_PATH.'svg-favicon.php'));
            set_transient( $transient, $tokens );
        }

        $comments = [];

        foreach($tokens as $token)
        {
            if( !empty($token[0]) ) {
                if($token[0] === T_DOC_COMMENT) {
                    $comments[] = $token[1] ?? [];
                }
            }
        }

        $re = '/\* version:\s+(?P<version_number>.*)/mi';

        preg_match_all($re, $comments[0], $matches, PREG_SET_ORDER, 0);

        return $matches[0]['version'] ?? '0.0.0';
    }

    public function version(){

        if( defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local' ){
            delete_transient( $this->domain.'_getVersionNumber' );
            return rand();
        }

        return $this->getVersionNumber();
    }

}