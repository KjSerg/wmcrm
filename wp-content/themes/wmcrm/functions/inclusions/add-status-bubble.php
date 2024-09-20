<?php

function wph_cut_the_title($title) {
	$title = esc_attr($title);
	$findthese = array(
		'#Защищено:#',
		'#Личное:#'
	);
	$replacewith = array(
		'', //можно указать замену для "Защищено"
		''  //можно указать замену для "Личное"
	);
	$title = preg_replace($findthese, $replacewith, $title);
	return $title;
}
add_filter('the_title', 'wph_cut_the_title');


function true_status_custom()
{
	register_post_status('archive', array(
		'label' => 'Закрито',
		'label_count' => _n_noop('Закрито <span class="count">(%s)</span>', 'Закрито <span class="count">(%s)</span>'),
		'public' => true,
		'show_in_admin_status_list' => true // если установить этот параметр равным false, то следующий параметр можно удалить
	));
}

add_action('init', 'true_status_custom');

function true_append_post_status_list()
{
	global $post;
	$optionselected = '';
	$statusname = '';
	if ($post->post_type == 'projects') { // если хотите, можете указать тип поста, для которого регистрируем статус, а можете и вовсе избавиться от этого условия
		if ($post->post_status == 'archive') { // если посту присвоен статус архива
			$optionselected = ' selected="selected"';
			$statusname = "$('#post-status-display').text('Закрито');";
		}
		/*
		 * Код jQuery мы просто выводим в футере
		 */
		echo "<script>
		jQuery(function($){
			$('select#post_status').append('<option value=\"archive\"$optionselected>Закрито</option>');
			$statusname
		});
		</script>";
	}
}

add_action('admin_footer-post-new.php', 'true_append_post_status_list'); // страница создания нового поста
add_action('admin_footer-post.php', 'true_append_post_status_list'); // страница редактирования поста

function true_status_display($statuses)
{
	global $post;
	if (get_query_var('post_status') != 'archive') { // проверка, что мы не находимся на странице всех постов данного статуса
		if ($post->post_status == 'archive') { // если статус поста - Архив
			return array('Закрито');
		}
	}
	return $statuses;
}

add_filter('display_post_states', 'true_status_display');

add_action('admin_footer-edit.php', 'add_status');

function add_status()
{
	echo "<script>
	jQuery(document).ready( function($) { 
		$( 'select[name=\"_status\"]' ).append( '<option value=\"archive\">Закрито</option>' );
	});
	</script>";
}

