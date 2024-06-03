<?php
add_action('init', 'add_post_thumbs_in_post_list_table', 20);
function add_post_thumbs_in_post_list_table()
{
	// проверим какие записи поддерживают миниатюры
	$supports = get_theme_support('post-thumbnails');

	$ptype_names = array('team', 'stories', 'news'); // указывает типы для которых нужна колонка отдельно

	// Определяем типы записей автоматически 
	if (!isset($ptype_names)) {
		if ($supports === true) {
			$ptype_names = get_post_types(array('public' => true), 'names');
			$ptype_names = array_diff($ptype_names, array('attachment'));
		} // для отдельных типов записей
		elseif (is_array($supports)) {
			$ptype_names = $supports[0];
		}
	}

	// добавляем фильтры для всех найденных типов записей
	foreach ($ptype_names as $ptype) {
		add_filter("manage_{$ptype}_posts_columns", 'add_thumb_column');
		add_action("manage_{$ptype}_posts_custom_column", 'add_thumb_value', 10, 2);
	}
}

// добавим колонку
function add_thumb_column($columns)
{
	// подправим ширину колонки через css
	add_action('admin_notices', function () {
		echo '
			<style>
				.column-thumbnail{ width:90px; text-align:center; }
			</style>';
	});

	$num = 1; // после какой по счету колонки вставлять новые

	$new_columns = array('thumbnail' => __('Thumbnail'));

	return array_slice($columns, 0, $num) + $new_columns + array_slice($columns, $num);
}

// заполним колонку
function add_thumb_value($colname, $post_id)
{
	if ('thumbnail' == $colname) {
		$width = $height = 100;

		// миниатюра
		if ($thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true)) {
			$thumb = wp_get_attachment_image($thumbnail_id, array($width, $height), true);
		} // из галереи...
		elseif ($attachments = get_children(array(
			'post_parent' => $post_id,
			'post_mime_type' => 'image',
			'post_type' => 'attachment',
			'numberposts' => 1,
			'order' => 'DESC',
		))
		) {
			$attach = array_shift($attachments);
			$thumb = wp_get_attachment_image($attach->ID, array($width, $height), true);
		} elseif (function_exists('carbon_get_post_meta') && $img = carbon_get_post_meta($post_id, 'icon')) {
			$thumb = wp_get_attachment_image($img, array($width, $height), true);
		}

		echo empty($thumb) ? ' ' : $thumb;
	}
}