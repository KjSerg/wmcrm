<?php

namespace FOF\SVGFAVICON\Admin;

use FOF\SVGFAVICON\Models\PluginInfo;

class AdminSetup
{
    private PluginInfo $pluginInfo;

	protected array $admin_screens = [
		'appearance_page_svg-favicon-settings',
	];
	
	public function __construct(PluginInfo $plugin_info){
		$this->pluginInfo = $plugin_info;
    }

    public function enqueueStyles($screen)
    {
        if (!in_array($screen, $this->admin_screens)) {
            return;
        }

        wp_enqueue_style(
            $this->pluginInfo->domain. '-tippy',
            SVGFAVICON_PLUGIN_URL . 'assets/css/tippy.css',
            [],
            $this->pluginInfo->version(),
            'all'
        );

        wp_enqueue_style(
            $this->pluginInfo->domain. '-tippy-shift-away',
            SVGFAVICON_PLUGIN_URL . 'assets/css/shift-away.css',
            [$this->pluginInfo->domain. '-tippy'],
            $this->pluginInfo->version(),
            'all'
        );

        wp_enqueue_style(
            $this->pluginInfo->domain. '-pickr',
            SVGFAVICON_PLUGIN_URL . 'assets/css/nano.min.css',
            [],
            $this->pluginInfo->version(),
            'all'
        );

        wp_enqueue_style(
            $this->pluginInfo->domain. '-admin',
            SVGFAVICON_PLUGIN_URL . 'assets/css/admin.css',
            [],
            $this->pluginInfo->version(),
            'all'
        );
    }

	public function enqueueAdminScripts($screen)
	{
		if (!in_array($screen, $this->admin_screens)) {
			return;
		}

		wp_enqueue_script(
			$this->pluginInfo->domain . '-admin',
			SVGFAVICON_PLUGIN_URL . 'assets/js/app-bundle.js',
			[
				'jquery', 
				'wp-i18n', 
				'wp-element',
			],
			$this->pluginInfo->version(),
			true
		);

		$utils = array_merge($this->pluginInfo->ajaxUtils(), []);

	 	$script = 'const '.str_replace('-', '_', $this->pluginInfo->domain).'_utils = ' . json_encode($utils, JSON_NUMERIC_CHECK) . ';';

	 	wp_add_inline_script($this->pluginInfo->domain . '-admin', $script, 'before');
	}
}