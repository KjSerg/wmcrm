<?php
/**
 * wmcrm functions and definitions
 *
 * @package wmcrm
 */

function wmcrm_scripts() {

	wp_enqueue_style( 'wmcrm-style', get_stylesheet_uri() );

	wp_enqueue_style( 'wmcrm-fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-jquery-ui', 'https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css', array(), '1.0' );

	wp_enqueue_style( 'wmcrm-app', get_template_directory_uri() . '/assets/css/app.css', array(), '1.0.4.0' );

	wp_enqueue_script( 'wmcrm-app-scripts', get_template_directory_uri() . '/assets/js/app.js', array(), '1.0.7.3', true );

	wp_localize_script( 'ajax-script', 'AJAX', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_enqueue_scripts', 'wmcrm_scripts' );

get_template_part( 'functions/helpers' );
get_template_part( 'functions/settings' );
get_template_part( 'functions/carbon-settings' );
get_template_part( 'functions/ajax-functions' );
get_template_part( 'functions/parsing-functions' );
get_template_part( 'functions/admin-pages/parse-settings-page' );
get_template_part( 'functions/mails' );
get_template_part( 'functions/cron-events' );
get_template_part( 'functions/telegram' );
get_template_part( 'components/login' );
get_template_part( 'components/projects-components' );
get_template_part( 'components/Comments' );
get_template_part( 'components/Comment' );
get_template_part( 'components/components' );
get_template_part( 'components/profile' );
get_template_part( 'components/create-project' );
get_template_part( 'components/edit-project' );
get_template_part( 'components/events-section' );
get_template_part( 'components/users-page' );
get_template_part( 'components/Board' );
get_template_part( 'components/the-board' );
get_template_part( 'functions/actions-functions' );

function custom_logout_and_redirect(): void {
	$user_id = get_current_user_id();
	if ( $user_id && ! is_current_user_admin() ) {
		$fired = carbon_get_user_meta( $user_id, 'fired' );
		if ( $fired ) {
			if ( is_user_logged_in() ) {
				wp_logout();
				wp_redirect( home_url() );
				exit();
			}
		}
	}
}

add_action( 'init', 'custom_logout_and_redirect' );

add_action( 'init', 'disable_smilies' );

function disable_smilies() {
	update_option( 'use_smilies', 0 );
}

add_action( 'init', 'set_current_user_status' );
function set_current_user_status() {
	if ( $user_id = get_current_user_id() ) {
		carbon_set_user_meta( $user_id, 'last_time_online', time() );
	}
}

add_filter( 'auth_cookie_expiration', 'cookie_expiration_new', 20, 3 );
function cookie_expiration_new( $expiration, $user_id, $remember ) {
	if ( $remember == true ) {
		return 400 * DAY_IN_SECONDS;
	}

	return 365 * DAY_IN_SECONDS;
}

function custom_mime_types( $mimes ) {
	$mimes['doc']  = 'application/msword';
	$mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

	return $mimes;
}

add_filter( 'upload_mimes', 'custom_mime_types' );

// Вимкнення oEmbed
remove_filter( 'the_content', [ $GLOBALS['wp_embed'], 'autoembed' ], 8 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );



