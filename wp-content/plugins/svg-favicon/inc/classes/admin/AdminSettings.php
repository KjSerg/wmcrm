<?php

namespace FOF\SVGFAVICON\Admin;

use FOF\SVGFAVICON\Models\PluginInfo;

class AdminSettings
{
    protected PluginInfo $pluginInfo;

    public function __construct(PluginInfo $plugin_info) {
        $this->pluginInfo = $plugin_info;
    }

    public function addSettingsPage(){

        add_theme_page(
            sprintf( __("%s", 'svg-favicon'), $this->pluginInfo->settings['page_title'] ),
            sprintf( __("%s", 'svg-favicon'), $this->pluginInfo->settings['menu_title'] ),
            'manage_options',
            $this->pluginInfo->settings['slug'],
            [
                $this,
                'createAdminPage'
            ],
            76,
        );
    }

    public function createAdminPage(){
        echo '<div id="svg-favicon-settings">Loading...</div>';
    }
}