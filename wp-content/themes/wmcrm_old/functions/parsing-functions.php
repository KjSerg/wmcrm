<?php

add_action( 'wp_ajax_nopriv_start_parsing', 'start_parsing' );
add_action( 'wp_ajax_start_parsing', 'start_parsing' );
function start_parsing() {
	$domain  = $_POST['domain'] ?? '';
	$api_key = $_POST['api_key'] ?? '';
	if ( $domain && $api_key ) {
		$apikey        = $api_key;
		$page          = '';
		$action        = 'get_all_tasks';
		$extra         = 'html,files,relations,subtasks';
		$hash          = md5( $action . $apikey );
		$end_point_url = $domain . 'api/admin/v2/?';
		$end_point_url .= "action=$action";
		$end_point_url .= '&extra=' . $extra;
		if ( $page ) {
			$end_point_url .= "&page=$page";
		}
		$end_point_url .= "&hash=$hash";
		$res           = send_request( $end_point_url );
		$status        = $res['status'] ?? '';
		$data          = $res['data'] ?? '';
		if ( $status == 'ok' && $data ) {
			foreach ( $data as $item ) {
				create_project( $item );
			}
		}
	} else {
		echo 'Потрібно заповнити поля!';
	}
	die();
}

add_action( 'wp_ajax_nopriv_update_projects', 'update_projects' );
add_action( 'wp_ajax_update_projects', 'update_projects' );
function update_projects() {
	$domain  = $_GET['domain'] ?? '';
	$api_key = $_GET['api_key'] ?? '';
	if ( $domain && $api_key ) {
		$apikey        = $api_key;
		$page          = '';
		$action        = 'get_all_tasks';
		$extra         = 'html,files,relations,subtasks';
		$hash          = md5( $action . $apikey );
		$end_point_url = $domain . 'api/admin/v2/?';
		$end_point_url .= "action=$action";
		$end_point_url .= '&extra=' . $extra;
		$end_point_url .= "&hash=$hash";
		$res           = send_request( $end_point_url );
		$status        = $res['status'] ?? '';
		$data          = $res['data'] ?? '';
		if ( $status == 'ok' && $data ) {
			foreach ( $data as $item ) {
				update_project( $item );
			}
		}
	} else {
		echo 'Потрібно заповнити поля!';
	}
	die();
}

add_action( 'wp_ajax_nopriv_get__comments', 'get__comments' );
add_action( 'wp_ajax_get__comments', 'get__comments' );
function get__comments() {
	$connect_id = $_POST['connect_id'] ?? '';
	$domain     = $_POST['domain'] ?? '';
	$api_key    = $_POST['api_key'] ?? '';
	$number     = $_POST['number'] ?? - 1;
	$offset     = $_POST['offset'] ?? 0;
	$int        = 0;
	if ( $domain && $api_key ) {
		$number = (int) $number;
		$args   = array(
			'post_type'      => 'projects',
			'post_status'    => 'publish',
			'posts_per_page' => $number,
		);
		if ( $offset > 0 ) {
			$offset         = (int) $offset;
			$int            = $offset;
			$args['offset'] = $offset;
		}
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$int              = $int + 1;
				$string           = "<strong>Project number #<span class='offset-num-js'>$int</span> </strong><br>";
				$id               = get_the_ID();
				$worksection_page = carbon_get_post_meta( $id, 'worksection_page' );
				echo $string;
				if ( $worksection_page ) {
					set_worksection_comments( array(
						'domain'           => $domain,
						'api_key'          => $api_key,
						'worksection_page' => $worksection_page,
						'project_id'       => $id,
						'project_number'   => $int,
					) );
				}
				if ( $connect_id ) {
					$_index   = false;
					$sessions = carbon_get_theme_option( 'sessions' ) ?: array();
					if ( $sessions ) {
						foreach ( $sessions as $session_index => $session ) {
							$_connect_id = $session['connect_id'];
							if ( $connect_id == $_connect_id ) {
								$_index = $session_index;
							}
						}
					}
					if ( $_index !== false ) {
						if ( $sessions[ $_index ]['last_message'] != $string ) {
							$sessions[ $_index ]['last_message'] = $string;
						}
					} else {
						$sessions[] = array(
							'last_message' => $string,
							'connect_id'   => $connect_id,
						);
					}
					carbon_set_theme_option( 'sessions', $sessions );
				}
			}
		}
		if ( $int >= wp_count_posts( 'projects' )->publish ) {
			echo 'done';
		}
		wp_reset_postdata();
		wp_reset_query();
	} else {
		echo 'Потрібно заповнити поля!';
	}
	die();
}

add_action( 'wp_ajax_nopriv_clear_all_comments', 'clear_all_comments' );
add_action( 'wp_ajax_clear_all_comments', 'clear_all_comments' );
function clear_all_comments() {
	$args  = array(
		'post_type'      => 'comments',
		'posts_per_page' => - 1,
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			wp_delete_post( get_the_ID() );
		}
	}
	wp_reset_postdata();
	wp_reset_query();
	die();
}

add_action( 'wp_ajax_nopriv_get_status_connect', 'get_status_connect' );
add_action( 'wp_ajax_get_status_connect', 'get_status_connect' );
function get_status_connect() {
	$connect_id = $_POST['connect'] ?? '';
	$time       = time();
	if ( $connect_id ) {
		$_index   = false;
		$sessions = carbon_get_theme_option( 'sessions' ) ?: array();
		if ( $sessions ) {
			foreach ( $sessions as $session_index => $session ) {
				$_connect_id = $session['connect_id'];
				if ( $connect_id == $_connect_id ) {
					echo "<div data-time='$time'>" . $session['last_message'] . "</div>";
				}
			}
		}
	}
	die();
}

function set_worksection_comments( $args = array() ) {
	$apikey         = $args['api_key'] ?? '';
	$domain         = $args['domain'] ?? '';
	$page           = $args['worksection_page'] ?? '';
	$project_id     = $args['project_id'] ?? 0;
	$project_number = $args['project_number'] ?? 0;
	if ( $project_number ) {
		setcookie( 'project_number', $project_number, ( ( time() + 3600 ) * 24 ) );
	}
	carbon_set_post_meta( $project_id, 'worksection_comment_ids', '' );
	if ( $page ) {
		$action        = 'get_comments';
		$extra         = 'files';
		$end_point_url = $domain . 'api/admin/v2/?';
		$end_point_url .= "action=$action";
		$end_point_url .= '&extra=' . $extra;
		$end_point_url .= "&page=$page";
		$hash          = md5( $page . $action . $apikey );
		$end_point_url .= "&hash=$hash";
		$res           = send_request( $end_point_url );
		$status        = $res['status'] ?? '';
		$data          = $res['data'] ?? '';
		if ( $status == 'ok' && $data ) {
			$comments_ids = array();
			foreach ( $data as $item ) {
				$comment_id = create_new_comment( $item );
				if ( $comment_id ) {
					carbon_set_post_meta( $comment_id, 'comment_project_id', $project_id );
					$comments_ids[] = $comment_id;
				}
				echo " [ID проєкта: $project_id]  <br>";
			}
			if ( $project_id ) {
				$c = count( $comments_ids );
				echo "<br><em>Кількість коментарів $c</em><br><hr>";
				$comments_ids_str = implode( ',', $comments_ids );
				carbon_set_post_meta( $project_id, 'worksection_comment_ids', $comments_ids_str );
			}
		}
	}
}

function create_new_comment( $item ) {
	$text       = $item['text'] ?? '';
	$date_added = $item['date_added'] ?? '';
	$user_from  = $item['user_from'] ?? array();
	$email      = $user_from['email'] ?? '';
	$hush       = md5( $email . $date_added . $text );
	$comment_id = (int) get_comment_by_hush( $hush );
	if ( $comment_id == 0 ) {
		$files     = $item['files'] ?? array();
		$timestamp = strtotime( $date_added );
		$post_data = array(
			'post_type'    => 'comments',
			'post_title'   => 'Comment',
			'post_status'  => 'publish',
			'post_content' => $text
		);
		if ( $timestamp !== false ) {
			$post_data['post_date'] = date( 'Y-m-d H:i:s', $timestamp );
		}
		$comment_id = wp_insert_post( $post_data, true );
		if ( ! is_wp_error( $comment_id ) ) {
			carbon_set_post_meta( $comment_id, 'comment_worksection_date_added', $timestamp ?? '' );
			carbon_set_post_meta( $comment_id, 'comment_project_hush', $hush );
			if ( $user_from ) {
				carbon_set_post_meta( $comment_id, 'worksection_user_email', $email );
				carbon_set_post_meta( $comment_id, 'worksection_user_name', $user_from['name'] ?? '' );
			}
			if ( $files ) {
				$files_arr = array();
				foreach ( $files as $file ) {
					$files_arr[] = array(
						'worksection_page' => $file['page']
					);
				}
				carbon_set_post_meta( $comment_id, 'comment_worksection_files', $files_arr );
			}
			echo "<div class='comment__item'>Створено коментар ID: $comment_id </div>";

			return $comment_id;
		} else {
			return 0;
		}
	} else {
		echo "<div class='comment__item'>Коментар ID: $comment_id вже існує </div>";

		return $comment_id;
	}
}

function get_comment_by_hush( $hush ) {
	$res   = 0;
	$args  = array(
		'post_type'      => 'comments',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array(
				'key'   => '_comment_project_hush',
				'value' => $hush,
			),
		),
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$res = get_the_ID();
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $res;
}



function create_project( $item, $parent = 0 ) {
	$id          = $item['id'] ?? '';
	$name        = $item['name'] ?? '';
	$page        = $item['page'] ?? '';
	$status      = $item['status'] ?? '';
	$priority    = $item['priority'] ?? '';
	$date_added  = $item['date_added'] ?? '';
	$date_closed = $item['date_closed'] ?? '';
	$html        = $item['text'] ?? '';
	$user_from   = $item['user_from'] ?? array();
	$user_to     = $item['user_to'] ?? array();
	$project     = $item['project'] ?? array();
	$tags        = $item['tags'] ?? array();
	$files       = $item['files'] ?? array();
	$child       = $item['child'] ?? array();
	$project_id  = get_project_by_worksection_id( $id );
	if ( $project_id === 0 ) {
		$timestamp = strtotime( $date_added );
		$post_data = array(
			'post_type'    => 'projects',
			'post_title'   => $name,
			'post_status'  => 'publish',
			'post_content' => $html
		);
		if ( $timestamp !== false ) {
			$post_data['post_date'] = date( 'Y-m-d H:i:s', $timestamp );
		}
		if ( $parent != 0 ) {
			$post_data['post_parent'] = $parent;
		}
		$project_id = wp_insert_post( $post_data, true );
		$post       = get_post( $project_id );
		if ( ! is_wp_error( $project_id ) && $post ) {
			carbon_set_post_meta( $project_id, 'worksection_id', $id );
			carbon_set_post_meta( $project_id, 'worksection_page', $page );
			carbon_set_post_meta( $project_id, 'worksection_status', $status );
			carbon_set_post_meta( $project_id, 'worksection_priority', $priority );
			carbon_set_post_meta( $project_id, 'worksection_date_added', $timestamp );
			if ( $date_closed ) {
				$date_closed_timestamp = strtotime( $date_closed );
				carbon_set_post_meta( $project_id, 'worksection_date_closed', $date_closed_timestamp );
			}
			if ( $user_from ) {
				carbon_set_post_meta( $project_id, 'worksection_user_from_id', $user_from['id'] );
				carbon_set_post_meta( $project_id, 'worksection_user_from_email', $user_from['email'] );
				carbon_set_post_meta( $project_id, 'worksection_user_from_name', $user_from['name'] );
			}
			if ( $user_to ) {
				carbon_set_post_meta( $project_id, 'worksection_user_to_id', $user_to['id'] );
				carbon_set_post_meta( $project_id, 'worksection_user_to_email', $user_to['email'] );
				carbon_set_post_meta( $project_id, 'worksection_user_to_name', $user_to['name'] );
			}
			if ( $files ) {
				$files_arr = array();
				foreach ( $files as $file ) {
					$files_arr[] = array(
						'worksection_page' => $file['page']
					);
				}
				carbon_set_post_meta( $project_id, 'worksection_files', $files_arr );
			}
			if ( $project ) {
				wp_set_post_terms( $project_id, get_category_array_id_by_name( $project['name'] ), 'categories', true );
			}
			echo "Проект '$name' створено [ID: $project_id] <em>Батьківський елемент: $parent</em><br> <hr>";
		} else {
			echo "Проект '$name' не стоврено: <strong>" . $project_id->get_error_message() . '</strong><br><hr>';
		}
	} else {
		echo "Проект '$name' вже існує ";
		$post_data = array(
			'ID' => $project_id,
		);
		if ( $parent != 0 ) {
			$post_data['post_parent'] = $parent;
			$project_id               = wp_update_post( $post_data, true );
			if ( ! is_wp_error( $project_id ) ) {
				echo "і він оновлений <em>Батьківський елемент: $parent</em><br> <hr>";
			}
		}


	}
	if ( $child ) {
		foreach ( $child as $child_item ) {
			create_project( $child_item, $project_id );
		}
	}
}

function update_project( $item, $parent = 0 ) {
	$id          = $item['id'] ?? '';
	$name        = $item['name'] ?? '';
	$page        = $item['page'] ?? '';
	$status      = $item['status'] ?? '';
	$priority    = $item['priority'] ?? '';
	$date_added  = $item['date_added'] ?? '';
	$date_closed = $item['date_closed'] ?? '';
	$html        = $item['text'] ?? '';
	$user_from   = $item['user_from'] ?? array();
	$user_to     = $item['user_to'] ?? array();
	$project     = $item['project'] ?? array();
	$tags        = $item['tags'] ?? array();
	$files       = $item['files'] ?? array();
	$child       = $item['child'] ?? array();
	$project_id  = get_project_by_worksection_id( $id );
	$timestamp   = strtotime( $date_added );
	$post_data   = array(
		'post_type'    => 'projects',
		'post_title'   => $name,
		'post_status'  => 'publish',
		'post_content' => $html
	);
	if ( $timestamp !== false ) {
		$post_data['post_date'] = date( 'Y-m-d H:i:s', $timestamp );
	}
	if ( $parent != 0 ) {
		$post_data['post_parent'] = $parent;
	}
	if ( $project_id === 0 ) {
		$project_id = wp_insert_post( $post_data, true );
	} else {
		$post_data['ID'] = $project_id;
		$project_id      = wp_update_post( $post_data, true );
	}
	$post = get_post( $project_id );
	if ( ! is_wp_error( $project_id ) && $post ) {
		carbon_set_post_meta( $project_id, 'worksection_id', $id );
		carbon_set_post_meta( $project_id, 'worksection_page', $page );
		carbon_set_post_meta( $project_id, 'worksection_status', $status );
		carbon_set_post_meta( $project_id, 'worksection_priority', $priority );
		carbon_set_post_meta( $project_id, 'worksection_date_added', $timestamp );
		if ( $date_closed ) {
			$date_closed_timestamp = strtotime( $date_closed );
			carbon_set_post_meta( $project_id, 'worksection_date_closed', $date_closed_timestamp );
		}
		if ( $user_from ) {
			carbon_set_post_meta( $project_id, 'worksection_user_from_id', $user_from['id'] );
			carbon_set_post_meta( $project_id, 'worksection_user_from_email', $user_from['email'] );
			carbon_set_post_meta( $project_id, 'worksection_user_from_name', $user_from['name'] );
		}
		if ( $user_to ) {
			carbon_set_post_meta( $project_id, 'worksection_user_to_id', $user_to['id'] );
			carbon_set_post_meta( $project_id, 'worksection_user_to_email', $user_to['email'] );
			carbon_set_post_meta( $project_id, 'worksection_user_to_name', $user_to['name'] );
		}
		if ( $files ) {
			$files_arr = array();
			foreach ( $files as $file ) {
				$files_arr[] = array(
					'worksection_page' => $file['page']
				);
			}
			carbon_set_post_meta( $project_id, 'worksection_files', $files_arr );
		}
		if ( $project ) {
			wp_set_post_terms( $project_id, get_category_array_id_by_name( $project['name'] ), 'categories', true );
		}
		if ( $tags ) {
			foreach ( $tags as $tag_id => $tag_name ) {
				wp_set_post_terms( $project_id, get_tag_array_id_by_name( $tag_name ), 'tags', true );
			}
		}
		echo "Проект '$name' оновлено [ID: $project_id] <em>Батьківський елемент: $parent</em><br> <hr>";
	} else {
		echo "Проект '$name' НЕ оновлено: <strong>" . $project_id->get_error_message() . '</strong><br><hr>';
	}
	if ( $child ) {
		$children = array();
		foreach ( $child as $child_item ) {
			$child_id         = $child_item['id'] ?? '';
			$child_project_id = get_project_by_worksection_id( $child_id );
			$children[]       = $child_project_id;
		}
		carbon_set_post_meta( $project_id, 'worksection_children_ids', implode( ',', $children ) );
	}
}

function get_category_array_id_by_name( $name ) {

	$base64_string = 'categories_' . md5( $name );
	if ( false !== ( $res = get_transient( $base64_string ) ) ) {
		$term_id = $res;

		return array( $term_id );
	}

	$res = array();
	if ( $name ) {
		$term = get_term_by( 'name', $name, 'categories' );
		if ( $term ) {
			$res[] = $term->term_id;
		} else {
			$term  = wp_insert_term( $name, 'categories' );
			$res[] = $term['term_id'];
		}
	}
	set_transient( $base64_string, $res[0], DAY_IN_SECONDS );

	return $res;
}

function get_tag_array_id_by_name( $name ) {

	$base64_string = 'tags_' . md5( $name );
	if ( false !== ( $res = get_transient( $base64_string ) ) ) {
		$term_id = $res;

		return array( $term_id );
	}
	$res = array();
	if ( $name ) {
		$term = get_term_by( 'name', $name, 'tags' );
		if ( $term ) {
			$res[] = $term->term_id;
		} else {
			$term  = wp_insert_term( $name, 'tags' );
			$res[] = $term['term_id'];
		}
	}
	set_transient( $base64_string, $res[0], DAY_IN_SECONDS );

	return $res;
}

function get_project_by_worksection_id( $id ) {
	$base64_string = 'project_' . $id;
	if ( false !== ( $res = get_transient( $base64_string ) ) ) {
		return $res;
	}
	$res   = 0;
	$args  = array(
		'post_type'      => 'projects',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array(
				'key'   => '_worksection_id',
				'value' => $id,
			),
		),
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$res = get_the_ID();
		}
	}
	wp_reset_postdata();
	wp_reset_query();
	set_transient( $base64_string, $res, DAY_IN_SECONDS );

	return $res;
}

function my_handle_attachment( $file_handler, $post_id = 0, $set_thu = false ) {

	if ( $_FILES[ $file_handler ]['error'] !== UPLOAD_ERR_OK ) {
		__return_false();
	}

	require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
	require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
	require_once( ABSPATH . "wp-admin" . '/includes/media.php' );

	return media_handle_upload( $file_handler, $post_id );
}