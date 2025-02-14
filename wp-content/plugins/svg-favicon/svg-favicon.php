<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://a415production.com
 * @package           svg-favicon
 *
 * @wordpress-plugin
 * Plugin Name:       SVG Favicon
 * Plugin URI:        https://a415production.com/products/plugins/svg-plugin
 * Description:       The official SVG Favicon plugin.
 * Version:           1.5.2
 * Author:            a 415 Production
 * Author URI:        https://a415production.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       svg-favicon
 * Domain Path:       /languages
 */
 
 if (!defined('WPINC')) {
     die();
 }
 
 use FOF\SVGFAVICON\Services\ActivatorDeactivator;
 
 define('SVGFAVICON_PLUGIN_URL', plugin_dir_url(__FILE__));
 define('SVGFAVICON_PLUGIN_PATH', plugin_dir_path(__FILE__));

require __DIR__ . '/vendor/autoload.php';
require 'SVGFAVICONPsr4AutoloaderClass.php';
 
 $autoloader = new SVGFAVICONPsr4AutoloaderClass();
 
 $autoloader->addNamespace(
     'FOF\SVGFAVICON',
     'inc/classes'
 );
 
 $autoloader->addNamespace(
     'FOF\SVGFAVICON\Admin',
     'inc/classes/admin'
 );
 
 $autoloader->addNamespace(
     'FOF\SVGFAVICON\Client',
     'inc/classes/client'
 );
 
 $autoloader->addNamespace(
     'FOF\SVGFAVICON\Models',
     'inc/classes/models'
 );
 
 $autoloader->addNamespace(
     'FOF\SVGFAVICON\Services',
     'inc/classes/services'
 );
 
 $autoloader->addNamespace(
     'FOF\SVGFAVICON\Traits',
     'inc/classes/traits'
 );
 
 $autoloader->addNamespace(
     'FOF\SVGFAVICON\Tools',
     'inc/classes/tools'
 );
 
 $autoloader->register();
 
 $activateDeactivate = new ActivatorDeactivator();
 
 register_activation_hook(__FILE__, [
     $activateDeactivate,
     'activate',
 ]);
 
 register_deactivation_hook(__FILE__, [
     $activateDeactivate,
     'deactivate',
 ]);
 
 \FOF\SVGFAVICON\KERNEL::get_instance()->init()->run();
 