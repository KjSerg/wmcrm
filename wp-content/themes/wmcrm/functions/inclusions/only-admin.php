<?php
function only_admin()
{
	if (!current_user_can('manage_options') && '/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF']) {
		wp_redirect(site_url());
	}
}
add_action( 'admin_init', 'only_admin', 1 );