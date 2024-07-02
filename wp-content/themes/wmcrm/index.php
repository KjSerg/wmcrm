<?php
/* Template Name: Шаблон головної сторінки */
$user_id  = get_current_user_id();
$is_admin = is_current_user_admin();
on_telegram_auth();
if ( $user_id ) {
	start_work_time();
	$route = $_GET['route'] ?? '';
	if ( $route == 'profile' ) {
		the_profile();
	} elseif ( $route == 'users' && $is_admin ) {
		the_users_page();
	} else {
		$projects = get_post_type_archive_link( 'projects' );

		header( 'Location: ' . $projects );
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


