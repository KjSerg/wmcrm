<?php
/**
 * wmcrm functions and definitions
 *
 * @package wmcrm
 */

function wmcrm_scripts() {
	wp_enqueue_style( 'wmcrm-style', get_stylesheet_uri() );

	wp_enqueue_style( 'wmcrm-jquery-ui', get_template_directory_uri() . '/assets/css/jquery-ui.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-reset', get_template_directory_uri() . '/assets/css/reset.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-aos', get_template_directory_uri() . '/assets/css/aos.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-quill', get_template_directory_uri() . '/assets/css/quill.snow.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-selectric', get_template_directory_uri() . '/assets/css/selectric.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-window', get_template_directory_uri() . '/assets/css/modal-window-style.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-window', get_template_directory_uri() . '/assets/css/modal-window-style.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-main', get_template_directory_uri() . '/assets/css/css.css', array(), '1.0' );

	wp_enqueue_script( 'wmcrm-jq', get_template_directory_uri() . '/assets/js/jq.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-selectric', get_template_directory_uri() . '/assets/js/jquery.selectric.min.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-aos', get_template_directory_uri() . '/assets/js/aos.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-window', get_template_directory_uri() . '/assets/js/modal-window-script.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-tm', get_template_directory_uri() . '/assets/js/tm.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-quill', get_template_directory_uri() . '/assets/js/quill.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-animations-scripts', get_template_directory_uri() . '/assets/js/animations.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-animations-jquery-ui', get_template_directory_uri() . '/assets/js/jquery-ui.js', array(), '1.0', true );

	wp_enqueue_script( 'wmcrm-index-scripts', get_template_directory_uri() . '/assets/js/index.js', array(), '1.0', true );

	wp_localize_script( 'ajax-script', 'AJAX', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_enqueue_scripts', 'wmcrm_scripts' );

get_template_part( 'functions/helpers' );
get_template_part( 'functions/settings' );
get_template_part( 'functions/carbon-settings' );
get_template_part( 'functions/ajax-functions' );
get_template_part( 'functions/parsing-functions' );
get_template_part( 'functions/admin-pages/parse-settings-page' );
get_template_part( 'components/login' );
get_template_part( 'components/projects-components' );
get_template_part( 'components/comments-project' );
get_template_part( 'components/components' );
get_template_part( 'components/create-project' );
get_template_part( 'components/edit-project' );
get_template_part( 'functions/mails' );