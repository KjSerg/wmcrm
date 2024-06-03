<?php
get_template_part('functions/inclusions/support-thumbnails');
get_template_part('functions/inclusions/nav-menus');
get_template_part('functions/inclusions/disable-content-editor');
get_template_part('functions/inclusions/wpcf7-setting');
get_template_part('functions/inclusions/custom-mime-types');
get_template_part('functions/inclusions/custom-admin-js');
//get_template_part('functions/inclusions/advanced-search');
//get_template_part('functions/inclusions/remove-taxonomy-permalink');
//get_template_part('functions/inclusions/remove-slug-cptui-permalink');
get_template_part('functions/inclusions/hide-admin-bar');
get_template_part('functions/inclusions/only-admin');
//get_template_part('functions/inclusions/carbon-fields-customize');
get_template_part('functions/inclusions/add-status-bubble');
get_template_part('functions/inclusions/add-admin-preview');

add_filter('get_the_archive_title', function ($title) {
    return preg_replace('~^[^:]+: ~', '', $title);
});

function hide_comments_menu() {
	remove_menu_page('edit-comments.php');
}

add_action('admin_menu', 'hide_comments_menu');

