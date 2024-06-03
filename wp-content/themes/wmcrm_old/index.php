<?php
/* Template Name: Шаблон головної сторінки */
$user_id = get_current_user_id();
if ( $user_id ) {
	$projects = get_post_type_archive_link( 'projects' );
	header( 'Location: ' . $projects );
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
get_footer(); ?>


