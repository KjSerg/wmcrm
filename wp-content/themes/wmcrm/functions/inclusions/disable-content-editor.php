<?php
function onwp_disable_content_editor()
{
	if(isset($_GET['post']) || isset($_POST['post_ID'])){
		$post_id = $_GET['post'] ?? ($_POST['post_ID'] ?? 0);
		$template_file = get_post_meta($post_id, '_wp_page_template', true);
		if (
			$template_file == 'contacts-page.php' ||
			$template_file == 'education-page.php' ||
			$template_file == 'index.php'
		) {
			remove_post_type_support('page', 'editor');
		}
	}



}

add_action('admin_init', 'onwp_disable_content_editor');