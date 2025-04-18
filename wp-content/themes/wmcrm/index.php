<?php
/* Template Name: Шаблон головної сторінки */
$user_id  = get_current_user_id();
$is_admin = is_current_user_admin();
on_telegram_auth();
if ( $user_id ) {
	start_work_time();
	$route    = filter_input( INPUT_GET, 'route' ) ?: '';
	if ( $route === 'board' && $is_admin ) {
		the_board();
		die();
	}
	if ( $route == 'profile' ) {
		the_profile();
	} elseif ( $route == 'users' && $is_admin ) {
		the_users_page();
	} else {
		$projects       = get_post_type_archive_link( 'projects' );
		$user_main_page = carbon_get_user_meta( $user_id, 'user_main_page' );
		$url = $projects;
		if($user_main_page && $user_main_page != ''){
			$url = $user_main_page;
		}
		header( 'Location: ' . $url);
		die();
	}
	die();
}
get_header();
$var          = variables();
$set          = $var['setting_home'];
$assets       = $var['assets'];
$url          = $var['url'];
$url_home     = $var['url_home'];
$id           = get_the_ID();
$isLighthouse = isLighthouse();
$size         = isLighthouse() ? 'thumbnail' : 'full';
the_login_page();
get_footer();
?>


