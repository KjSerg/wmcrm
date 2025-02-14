<?php

namespace FOF\SVGFAVICON\Services;
/**
 * Class ActivatorDeactivator
 * Activate/Deactivate plugin
 * Should only be initialized once
 * Should not be extended
 */

class ActivatorDeactivator {

	public function __construct() {}

	public function activate(){
		//error_log("Activated...");

		flush_rewrite_rules();
	}
	
	public function deactivate(){
		//error_log("deactivate!!!!");
        
        delete_transient('svg-favicon_getVersionNumber');
		flush_rewrite_rules();
	}
}