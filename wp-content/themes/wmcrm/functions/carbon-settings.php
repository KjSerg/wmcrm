<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'carbon_fields_register_fields', 'crb_attach_theme_options' );
function crb_attach_theme_options() {
	$labels = array(
		'plural_name'   => 'елементи',
		'singular_name' => 'елемент',
	);
	Container::make( 'theme_options', "Службова інформація" )
	         ->add_fields( array(
		         Field::make( "complex", "sessions" )->add_fields(
			         array(
				         Field::make( "text", "connect_id" ),
				         Field::make( "text", "last_message" ),
			         )
		         )
	         ) );

	Container::make( 'theme_options', "Зображення логотипів" )
	         ->add_fields( array(
		         Field::make( "image", "preloader", 'Логотип прелоадера' )->set_width( 50 )->set_required( true ),
		         Field::make( "image", "logo", 'Логотип ' )->set_width( 50 )->set_required( true ),
	         ) );
	Container::make( 'theme_options', 'Настройки telegram' )
	         ->set_page_parent( 'options-general.php' )
	         ->add_fields( array(
		         Field::make( 'text', 'telegram_bot_name' )->set_required( true ),
		         Field::make( 'text', 'telegram_token' )->set_required( true ),
	         ) );
}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_projects' );
function crb_attach_in_projects() {
	$labels = array(
		'plural_name'   => 'елементи',
		'singular_name' => 'елемент',
	);
	Container::make( 'post_meta', 'Основна інформація' )
	         ->show_on_post_type( 'projects' )
	         ->add_fields(
		         array(
			         Field::make( "text", "project_user_from_id", "ID користувача який поставив задачу" )->set_width( 50 ),
			         Field::make( "text", "project_user_from_name", "Імя користувача який поставив задачу" )->set_width( 50 ),
			         Field::make( "text", "project_users_to_id", "ID виконавців" )->set_width( 50 ),
			         Field::make( "text", "project_users_to_name", "Імя виконавців" )->set_width( 50 ),
			         Field::make( "text", "project_users_observer_id", "ID спостерігачів" )->set_width( 50 ),
			         Field::make( "text", "project_users_observer_name", "Імя спостерігачів" )->set_width( 50 ),
		         )
	         );

	Container::make( 'post_meta', 'Основні коментарі' )
	         ->show_on_post_type( 'projects' )
	         ->add_fields(
		         array(
			         Field::make( "textarea", "project_comment_ids", "ID коментарів" ),
		         )
	         );

	Container::make( 'post_meta', 'Worksection' )
	         ->show_on_post_type( 'projects' )
	         ->add_fields(
		         array(
			         Field::make( "separator", "crb_style_options1", "Основні данні" ),
			         Field::make( "text", "worksection_id", "ID worksection" ),
			         Field::make( "text", "worksection_page", "Worksection page" ),
			         Field::make( "text", "worksection_status", "Worksection status" ),
			         Field::make( "text", "worksection_priority", "Worksection priority" ),
			         Field::make( "separator", "crb_style_options2", "Менеджер або автор задачі" ),
			         Field::make( "text", "worksection_user_from_id", "Worksection ID користувача який поставив задачу" ),
			         Field::make( "text", "worksection_user_from_email", "Worksection email користувача який поставив задачу" ),
			         Field::make( "text", "worksection_user_from_name", "Worksection імя користувача який поставив задачу" ),
			         Field::make( "separator", "crb_style_options3", "Виконавець" ),
			         Field::make( "text", "worksection_user_to_id", "Worksection ID користувача якому поставлена задача" ),
			         Field::make( "text", "worksection_user_to_email", "Worksection email користувача якому поставлена задача" ),
			         Field::make( "text", "worksection_user_to_name", "Worksection імя користувача якому поставлена задача" ),
			         Field::make( "separator", "crb_style_options4", "Дата" ),
			         Field::make( "date_time", "worksection_date_added", "Worksection дата" )->set_storage_format( 'U' ),
			         Field::make( "date_time", "worksection_date_closed", "Worksection дата закриття" )->set_storage_format( 'U' ),
			         Field::make( "separator", "crb_style_options5", "Файли" ),
			         Field::make( "complex", "worksection_files", "Файли" )->add_fields( array(
				         Field::make( "text", "worksection_page", "Worksection page" ),
			         ) ),
			         Field::make( "separator", "crb_style_options10", "Дочірні елементи" ),
			         Field::make( "textarea", "worksection_children_ids", "ID дочірних елементи" ),
		         )
	         );

	Container::make( 'post_meta', 'Worksection коментарі' )
	         ->show_on_post_type( 'projects' )
	         ->add_fields(
		         array(
			         Field::make( "textarea", "worksection_comment_ids", "ID архівних коментарів" ),
		         )
	         );

}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_comments' );
function crb_attach_in_comments() {
	$labels = array(
		'plural_name'   => 'елементи',
		'singular_name' => 'елемент',
	);
	Container::make( 'post_meta', 'Проєкт' )
	         ->show_on_post_type( 'comments' )
	         ->add_fields(
		         array(
			         Field::make( "text", "comment_project_id", "ID проєка" ),
			         Field::make( "text", "comment_project_hush", "Hush коментаря" ),
		         )
	         );

	Container::make( 'post_meta', 'Worksection' )
	         ->show_on_post_type( 'comments' )
	         ->add_fields(
		         array(
			         Field::make( "separator", "crb_style_options1", "Основні данні" ),
			         Field::make( "text", "worksection_user_email", "Worksection email користувача" )->set_width( 50 ),
			         Field::make( "text", "worksection_user_name", "Worksection і`мя користувача" )->set_width( 50 ),
			         Field::make( "separator", "crb_style_options4", "Дата" ),
			         Field::make( "date_time", "comment_worksection_date_added", "Worksection дата" )->set_storage_format( 'U' ),
			         Field::make( "separator", "crb_style_options5", "Файли" ),
			         Field::make( "complex", "comment_worksection_files", "Файли" )->add_fields( array(
				         Field::make( "text", "worksection_page", "Worksection page" ),
			         ) )
		         )
	         );

}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_discussion' );
function crb_attach_in_discussion() {
	$labels = array(
		'plural_name'   => 'елементи',
		'singular_name' => 'елемент',
	);
	Container::make( 'post_meta', 'Інформація' )
	         ->show_on_post_type( 'discussion' )
	         ->add_fields(
		         array(
			         Field::make( "text", "discussion_project_id", "ID проєка" ),
			         Field::make( "text", "discussion_project_hush", "Hush коментаря" ),
			         Field::make( "checkbox", "discussion_is_service", "Службове повідомлення" ),
			         Field::make( "text", "discussion_read_users", "Прочитано користувачами" ),
			         Field::make( 'complex', 'discussion_files', __( 'Файли' ) )
			              ->add_fields( array(
				              Field::make( 'text', 'url', __( 'Посилання на файл' ) )
			              ) )
		         )
	         );
}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_events' );
function crb_attach_in_events() {
	$labels = array(
		'plural_name'   => 'елементи',
		'singular_name' => 'елемент',
	);
	Container::make( 'post_meta', 'Інформація' )
	         ->show_on_post_type( 'events' )
	         ->add_fields(
		         array(
			         Field::make( "text", "event_question", "Запитання" ),
			         Field::make( "complex", "event_answers", 'Варіанти відповіді' )
			              ->add_fields(
				              array(
					              Field::make( "text", "answer", 'Відповідь' ),
				              )
			              ),
			         Field::make( "checkbox", "event_anonymous", "Анонімне голосування" ),
			         Field::make( "checkbox", "event_multiple", "Вибір декількох варіантів" ),
		         )
	         );
	Container::make( 'post_meta', 'Результати' )
	         ->show_on_post_type( 'event_results' )
	         ->add_fields(
		         array(
			         Field::make( "text", "event_id", "ID опитування" ),
			         Field::make( "checkbox", "event_acquainted", "Ознайомлений" ),
			         Field::make( "text", "event_result_answers", "Відповіді" ),
		         )
	         );
}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_costs' );
function crb_attach_in_costs() {
	$labels = array(
		'plural_name'   => 'елементи',
		'singular_name' => 'елемент',
	);
	Container::make( 'post_meta', 'День' )
	         ->show_on_post_type( 'costs' )
	         ->add_tab( 'Інформація',
		         array(
			         Field::make( "text", "costs_date", "Дата" ),
			         Field::make( "select", "costs_status", "Статус робочого дня" )
			              ->set_options( array(
				              '0'  => 'Закінчено',
				              '1'  => 'Розпочато',
				              '-1' => 'Пауза',
			              ) ),
			         Field::make( "text", "costs_start", "Початок робочого дня" )->set_width( 50 ),
			         Field::make( "text", "costs_finish", "Кінець робочого дня" )->set_width( 50 ),
			         Field::make( "text", "costs_sum_hour", "Сума часу" )->set_width( 50 ),
			         Field::make( "text", "costs_sum", "Сума часу в мілісикундах за день" )->set_width( 50 ),
			         Field::make( "text", "costs_sum_hour_pause", "Сума паузи" )->set_width( 50 ),
			         Field::make( "text", "costs_sum_pause", "Сума паузи в мілісикундах за день" )->set_width( 50 ),
			         Field::make( "text", "costs_sum_hour_change", "Сума часу [змінено]" )->set_width( 50 ),
			         Field::make( "text", "costs_confirmed", "Підтвердженно" )->set_width( 50 ),
			         Field::make( "text", "costs_change_text", "Коментар" ),

			         Field::make( "complex", "costs_list", "Список зупинок" )
			              ->setup_labels( $labels )
			              ->add_fields( array(
				              Field::make( "date_time", "time_start", "Початок" )->set_width( 50 )->set_storage_format( 'U' ),
				              Field::make( "date_time", "time_finish", "Кінець" )->set_width( 50 )->set_storage_format( 'U' ),
			              ) ),

		         )
	         )
	         ->add_tab( 'Службові поля',
		         array(

			         Field::make( "textarea", "costs_data" ),
			         Field::make( "textarea", "pauses" ),
			         Field::make( "textarea", "post_data" ),
			         Field::make( "textarea", "res_data" ),
		         ) )
	         ->add_tab( 'Списки',
		         array(
			         Field::make( "complex", "costs_work_list", "Список відпрацювань" )
			              ->setup_labels( $labels )->set_width( 50 )
			              ->add_fields( array(
				              Field::make( "text", "start", "Початок" )->set_width( 50 ),
				              Field::make( "text", "finish", "Кінець" )->set_width( 50 ),
			              ) ),
			         Field::make( "complex", "costs_pause_list", "Список перерв" )
			              ->setup_labels( $labels )->set_width( 50 )
			              ->add_fields( array(
				              Field::make( "text", "start", "Початок" )->set_width( 50 ),
				              Field::make( "text", "finish", "Кінець" )->set_width( 50 ),
			              ) ),
		         )
	         )
	         ->add_tab( 'Розшифровки',
		         array(
			         Field::make( "complex", "costs_text_list", "Список" )
			              ->setup_labels( $labels )
			              ->add_fields( array(
				              Field::make( "text", "text", "Розшифровка текстом" ),
				              Field::make( "text", "user_agent"  )->set_width(50),
				              Field::make( "text", "user_ip"  )->set_width(50),
				              Field::make( "text", "unix"  )->set_width(33),
				              Field::make( "text", "status"  )->set_width(33),
				              Field::make( "text", "old_status"  )->set_width(33),
			              ) ),
		         )
	         );
}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_tags' );
function crb_attach_in_tags() {
	$labels = array(
		'plural_name'   => 'елементи',
		'singular_name' => 'елемент',
	);
	Container::make( 'term_meta', 'Налаштування' )
	         ->show_on_taxonomy( 'tags' )
	         ->add_fields(
		         array(
			         Field::make( "color", "tag_color", "Колір тега" ),
		         )
	         );
}

add_action( 'carbon_fields_register_fields', 'crb_attach_in_user' );
function crb_attach_in_user() {
	Container::make( 'user_meta', 'Користувач' )
	         ->add_fields(
		         array(
			         Field::make( "checkbox", "super_admin", "Суперадмін" ),
		         )
	         );
	Container::make( 'user_meta', 'Користувач' )
	         ->add_fields(
		         array(
			         Field::make( "checkbox", "fired", "Звільнений?" ),
			         Field::make( "image", "avatar", "Аватар користувача" ),
			         Field::make( "text", "position", "Посада користувача" ),
			         Field::make( "text", "user_tel", "Телефон користувача" ),
			         Field::make( "date", "birthday", "День народження" )->set_storage_format( 'd-m-Y' ),
			         Field::make( "text", "current_project", "Проєкт користувача" ),
			         Field::make( "text", "last_time_online", "Останній раз в мережі" ),
			         Field::make( "hidden", "user_project_filter", " " ),
		         )
	         );
	Container::make( 'user_meta', 'Сповіщення' )
	         ->add_fields(
		         array(
			         Field::make( "checkbox", "email_notification", "Email сповіщення" ),
			         Field::make( "checkbox", "comment_notification", "Коментар або згадка в коментарі" ),
			         Field::make( "checkbox", "project_notification", "Проєкт" ),
			         Field::make( "checkbox", "birthday_notification", "Дні народження" ),
		         )
	         );
	Container::make( 'user_meta', 'Telegram' )
	         ->add_fields(
		         array(
			         Field::make( "checkbox", "telegram_notification", "Телеграм сповіщення" ),
			         Field::make( 'text', 'telegram', 'Telegram' ),
			         Field::make( 'text', 'telegram_id', 'Telegram ID' ),
			         Field::make( 'text', 'telegram_image', 'Telegram аватар' ),
		         )
	         );
	Container::make( 'user_meta', 'Worksection' )
	         ->add_fields(
		         array(
			         Field::make( "text", "worksection_id", "ID користувача worksection" ),
		         )
	         );
	Container::make( 'post_meta', 'Налаштування' )
	         ->show_on_post_type( 'notification' )
	         ->add_fields(
		         array(
			         Field::make( "text", "notification_project_id", "ID проєкта" ),
			         Field::make( "text", "notification_comment_id", "ID коментаря" ),
		         )
	         );
}

add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
	get_template_part( 'vendor/autoload' );
	\Carbon_Fields\Carbon_Fields::boot();
}

add_filter( 'crb_media_buttons_html', function ( $html, $field_name ) {
	if (
		$field_name === 'text' ||
		$field_name === 'subtitle' ||
		$field_name === 'title'
	) {
		return;
	}

	return $html;
}, 10, 2 );
