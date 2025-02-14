<?php

namespace FOF\SVGFAVICON;

use FOF\SVGFAVICON\Admin\AdminSetup;
use FOF\SVGFAVICON\Admin\AdminSettings;
use FOF\SVGFAVICON\Client\ClientSetup;
use FOF\SVGFAVICON\Models\PluginInfo;
use FOF\SVGFAVICON\Services\i18n;
use FOF\SVGFAVICON\Services\Loader;
use FOF\SVGFAVICON\Services\OptionsService;
use FOF\SVGFAVICON\Services\Requests;
use FOF\SVGFAVICON\Services\SiteIconsService;
use FOF\SVGFAVICON\Services\SVGService;
use FOF\SVGFAVICON\Tools\Ajax;
use FOF\SVGFAVICON\Tools\Utils;
use FOF\SVGFAVICON\Traits\Singleton;

class KERNEL {

    use Singleton;

    protected i18n $i18n;
    protected AdminSettings $adminSettings;
    protected AdminSetup $adminSetup;
    protected ClientSetup $clientSetup;
    protected Loader $loader;
    protected PluginInfo $pluginInfo;
    protected Requests $requests;
    protected SiteIconsService $siteIcons;

    static public function init(){
        $self = self::$instance;

        $self->i18n             = new i18n();
        $self->loader           = new Loader();
        $self->pluginInfo       = new PluginInfo();
        $self->requests         = new Requests( new Ajax(), $self->pluginInfo, new Utils(), new SVGService($self->pluginInfo) );
        $self->adminSetup       = new AdminSetup($self->pluginInfo);
        $self->adminSettings    = new AdminSettings($self->pluginInfo);
        $self->siteIcons        = new SiteIconsService(new OptionsService());
        $self->clientSetup      = new ClientSetup($self->siteIcons);

        $self->setLocale();
        $self->defineAdminHooks();
        $self->defineAdminRequests();
        $self->definePublicHooks();

        return $self;
    }

    private function setLocale()
    {
        $self = self::$instance;

        $this->loader->add_action(
            'plugins_loaded',
            $self->i18n,
            'loadPluginTextdomain'
        );
    }

    private function definePublicHooks() {

        $this->loader->add_filter( 'wp_head', $this->clientSetup, 'svg_meta_tags', 25);
        $this->loader->add_filter( 'site_icon_meta_tags', $this->clientSetup, 'svg_favicon', 25);
    }

    private function  defineAdminRequests(){

        $requestMethods = array_values( array_filter(get_class_methods($this->requests), function($v){
            return !in_array($v,['__construct', 'setPayload']);
        }) );

        if( !empty($requestMethods) )
        {
            foreach($requestMethods as $method)
            {
                $this->loader->add_action(
                    'wp_ajax_'.$this->pluginInfo->domain.'-'.$method,
                    $this->requests,
                    $method
                );
            }
        }
    }

    private function defineAdminHooks(){
        $self = self::$instance;

        $self->loader->add_action(
            'admin_enqueue_scripts',
            $self->adminSetup,
            'enqueueStyles'
        );
        
        $self->loader->add_action(
            'admin_enqueue_scripts',
            $self->adminSetup,
            'enqueueAdminScripts'
        );

        $self->loader->add_action(
            'admin_menu',
            $self->adminSettings,
            'addSettingsPage'
        );
    }

    static public function run(){
        $self = self::$instance;
        $self->loader->run();
    }

}