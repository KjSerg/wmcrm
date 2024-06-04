<?php
add_action( 'wp_ajax_nopriv_login_user', 'login_user' );
add_action( 'wp_ajax_login_user', 'login_user' );
function login_user() {
	$time     = time();
	$res      = array();
	$var      = variables();
	$set      = $var['setting_home'];
	$assets   = $var['assets'];
	$url      = $var['url'];
	$email    = trim( $_POST['email'] ?? '' );
	$password = $_POST['password'] ?? '';
	if ( $email && $password ) {
		$user_id = email_exists( $email );
		$fired   = carbon_get_user_meta( $user_id, 'fired' );
		if ( $fired ) {
			$res['type'] = 'error';
			$res['msg']  = 'Помилка';
			echo json_encode( $res );
			die();
		}
		$user = wp_signon( array(
			'user_login'    => $email,
			'user_password' => $password,
			'remember'      => true,
		) );
		if ( is_wp_error( $user ) ) {
			$res['type'] = 'error';
			if ( $user_id = email_exists( $email ) ) {
				$res['msg'] = 'Невірний пароль';
			} else {
				$res['msg'] = 'Невірний email';
			}
		} else {
			$user_id          = email_exists( $email );
			$res['type']      = 'success';
			$res['user_id']   = $user_id;
			$res['is_reload'] = 'true';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Заповніть поля, щоб увійти';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_new_comment', 'new_comment' );
add_action( 'wp_ajax_new_comment', 'new_comment' );
function new_comment() {
	$res                = array();
	$user_id            = get_current_user_id();
	$text               = $_POST['text'] ?? '';
	$project_id         = $_POST['project_id'] ?? '';
	$update_comment_id  = $_POST['comment_id'] ?? '';
	$hush_text          = mb_strtolower( trim( $text ), 'UTF-8' );
	$res['$hush_text']  = $hush_text;
	$hush_text          = strip_tags( $hush_text, "<img>" );
	$res['$hush_text1'] = $hush_text;
	$text_test          = mb_strlen( $hush_text, 'UTF-8' ) > 0;
	$res['$text_test']  = $text_test;
	$res['$text']       = $text;
	if ( $user_id && $text && $project_id && $text_test ) {
		date_default_timezone_set( "Europe/Kiev" );
		$user          = get_user_by( 'id', $user_id );
		$timestamp     = time();
		$hush          = md5( $user_id . $project_id . $hush_text );
		$discussion_id = (int) get_discussion_by_hush( $hush );
		if ( $discussion_id == 0 ) {
			$project_comment_ids = carbon_get_post_meta( $project_id, 'project_comment_ids' );
			if ( $project_comment_ids ) {
				$project_comment_ids = explode( ',', $project_comment_ids );
			} else {
				$project_comment_ids = array();
			}
			$array         = get_text_with_users( $text );
			$text1         = $array['result_text'];
			$users_ids     = $array['users_ids'];
			$res['$text1'] = $text1;
			$post_data     = array(
				'post_type'    => 'discussion',
				'post_title'   => 'Comment',
				'post_status'  => 'publish',
				'post_content' => $text1,
				'post_author'  => $user_id
			);
			$comment_id    = 0;
			if ( $update_comment_id && get_post( $update_comment_id ) ) {
				$post_data['ID'] = $update_comment_id;
				$comment_id      = wp_update_post( $post_data, true );
				$arr             = carbon_get_post_meta( $comment_id, 'discussion_files' );
				if ( $arr ) {
					foreach ( $arr as $file ) {
						$file_url = $file['url'];
						if ( $file_url ) {
							$_id = attachment_url_to_postid( $file_url );
							if ( $_id ) {
								wp_delete_post( $_id );
							}
						}
					}
					carbon_set_post_meta( $comment_id, 'discussion_files', array() );
				}
			} else {
				$comment_id = wp_insert_post( $post_data, true );
			}

			if ( ! is_wp_error( $comment_id ) ) {
				carbon_set_post_meta( $comment_id, 'discussion_project_id', $project_id );
				wp_update_post( array(
					'ID' => $project_id,
				) );
				if ( ! $update_comment_id ) {
					$project_comment_ids[] = $comment_id;
					carbon_set_post_meta( $project_id, 'project_comment_ids', implode( ',', $project_comment_ids ) );
					create_cron_notification( array(
						'comment_id' => $comment_id,
						'project_id' => $project_id,
						'text'       => $text1,
						'users_ids'  => $users_ids,
					) );
				}
				if ( $_FILES ) {
					$files         = $_FILES["upfile"];
					$arr           = array();
					$res['$files'] = $files;
					foreach ( $files['name'] as $key => $value ) {
						if ( $files['name'][ $key ] ) {
							$file   = array(
								'name'     => $files['name'][ $key ],
								'type'     => $files['type'][ $key ],
								'tmp_name' => $files['tmp_name'][ $key ],
								'error'    => $files['error'][ $key ],
								'size'     => $files['size'][ $key ]
							);
							$_FILES = array( "file" => $file );
							foreach ( $_FILES as $file => $array ) {
								$f = my_handle_attachment( $file );
								if ( $f ) {
									$f_url = wp_get_attachment_url( $f );
									if ( $f_url ) {
										$arr[] = array( 'url' => $f_url );
									}
								}
							}
						}
					}
					if ( $arr ) {
						carbon_set_post_meta( $comment_id, 'discussion_files', $arr );
					}

				}
				ob_start();
				if ( $update_comment_id && get_post( $update_comment_id ) ) {
					the_comment_project( $comment_id );
					$res['comment_html_update'] = ob_get_clean();
					$res['comment_id']          = $comment_id;
				} else {
					the_comments( $project_id );
					$res['comments_html'] = ob_get_clean();
				}
				carbon_set_post_meta( $comment_id, 'discussion_project_hush', $hush );
			} else {
				$res['type'] = 'error';
				$res['msg']  = $comment_id->get_error_message();
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Ви вже це говорили';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_remove_comment', 'remove_comment' );
add_action( 'wp_ajax_remove_comment', 'remove_comment' );
function remove_comment() {
	$res                   = array();
	$user_id               = get_current_user_id();
	$comment_id            = $_POST['comment_id'] ?? '';
	$user                  = get_user_by( 'id', $user_id );
	$author_id             = get_post_field( 'post_author', $comment_id );
	$discussion_project_id = carbon_get_post_meta( $comment_id, 'discussion_project_id' );
	$test                  = true;
	if ( $author_id != $user_id ) {
		$test = false;
	}
	if ( $comment_id && get_post( $comment_id ) && $test ) {
		$arr = carbon_get_post_meta( $comment_id, 'discussion_files' );
		if ( $arr ) {
			foreach ( $arr as $file ) {
				$file_url = $file['url'];
				if ( $file_url ) {
					$_id = attachment_url_to_postid( $file_url );
					wp_delete_post( $_id );
				}
			}
			carbon_set_post_meta( $comment_id, 'discussion_files', array() );
		}
		if ( $project_comment_ids = carbon_get_post_meta( $discussion_project_id, 'project_comment_ids' ) ) {
			$project_comment_ids = explode( ',', $project_comment_ids );
			$key                 = array_search( $comment_id, $project_comment_ids );
			if ( $key !== false ) {
				unset( $project_comment_ids[ $key ] );
				carbon_set_post_meta( $discussion_project_id, 'project_comment_ids', implode( ',', $project_comment_ids ) );
			}
		}
		if ( $worksection_comment_ids = carbon_get_post_meta( $discussion_project_id, 'worksection_comment_ids' ) ) {
			$worksection_comment_ids = explode( ',', $worksection_comment_ids );
			$key                     = array_search( $comment_id, $worksection_comment_ids );
			if ( $key !== false ) {
				unset( $worksection_comment_ids[ $key ] );
				carbon_set_post_meta( $discussion_project_id, 'worksection_comment_ids', implode( ',', $worksection_comment_ids ) );
			}
		}
		if ( $users = get_users() ) {
			foreach ( $users as $_user ) {
				if ( $notification_id = get_user_notification_by_comment_id( $comment_id, $_user->ID ) ) {
					wp_delete_post( $notification_id );
				}
			}
		}
		if ( wp_delete_post( $comment_id ) ) {
			$res['type'] = 'success';
		} else {
			$res['type'] = 'error';
		}
	} else {
		$res['type'] = 'error';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_change_project_status', 'change_project_status' );
add_action( 'wp_ajax_change_project_status', 'change_project_status' );
function change_project_status() {
	$is_admin   = is_current_user_admin();
	$res        = array();
	$id         = $_POST['id'] ?? '';
	$status     = $_POST['status'] ?? 'publish';
	$old_status = get_post_status( $id );
	if ( $old_status != $status ) {
		if ( $is_admin ) {
			if ( $id && get_post( $id ) && $status ) {
				$post_data  = array(
					'post_type'   => 'projects',
					'post_status' => $status,
					'ID'          => (int) $id,
				);
				$project_id = wp_update_post( $post_data, true );
				if ( ! is_wp_error( $project_id ) ) {
					$res['$status']     = $status;
					$res['button_text'] = $status == 'publish' || $status == 'pending' ? 'Закрити проект' : 'Відкрити проект';
					$comment_id         = create_comment( array(
						'text'       => $status == 'publish' || $status == 'pending' ? 'Проєкт відкрито' : 'Проєкт закрито',
						'id'         => (int) $id,
						'is_service' => true,
					) );
					if ( $comment_id ) {
						ob_start();
						the_comments( $id );
						$res['comments_html'] = ob_get_clean();
					}
				} else {
					$res['type'] = 'error';
					$res['msg']  = $project_id->get_error_message();
				}
			} else {
				$res['type'] = 'error';
				$res['msg']  = 'Помилка';
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Помилка доступу';
		}
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_create_new_project', 'create_new_project' );
add_action( 'wp_ajax_create_new_project', 'create_new_project' );
function create_new_project() {
	$is_admin = is_current_user_admin();
	$user_id  = get_current_user_id();
	$res      = array();
	if ( $is_admin && $user_id ) {
		$title           = $_POST['title'] ?? '';
		$responsible     = $_POST['responsible'] ?? '';
		$text            = $_POST['text'] ?? '';
		$tags            = $_POST['tags'] ?? '';
		$edit_project_id = $_POST['project_id'] ?? '';
		$observers       = $_POST['observers'] ?? '';
		$post_status     = $_POST['post_status'] ?? '';
		$parent_id       = $_POST['parent_id'] ?? 0;
		if ( $title && $responsible ) {
			$responsible_ids   = array();
			$responsible_names = array();
			$observers_ids     = array();
			$observers_names   = array();
			$text              = $text ? get_text_with_users( $text )['result_text'] : '';
			$post_data         = array(
				'post_type'    => 'projects',
				'post_title'   => $title,
				'post_status'  => $post_status,
				'post_content' => $text,
				'post_author'  => $user_id
			);
			if ( $parent_id ) {
				$post_data['post_parent'] = (int) $parent_id;
			}
			$project_id = 0;
			$comment_id = 0;
			if ( $edit_project_id && get_post( $edit_project_id ) ) {
				$post_data['ID'] = $edit_project_id;
				$project_id      = wp_update_post( $post_data, true );
				$old_post_status = get_post_status( $edit_project_id );
				if ( $old_post_status != $post_status ) {
					$comment_id = create_comment( array(
						'text'       => $post_status == 'publish' || $post_status == 'pending' ? 'Проєкт відкрито' : 'Проєкт закрито',
						'id'         => (int) $edit_project_id,
						'is_service' => true,
					) );
				}
			} else {
				$project_id = wp_insert_post( $post_data, true );
				$comment_id = create_comment( array(
					'text'       => 'Проєкт створено',
					'id'         => (int) $project_id,
					'is_service' => true,
				) );
			}
			if ( $project_id && ! is_wp_error( $project_id ) ) {
				if ( ! is_array( $responsible ) ) {
					$responsible = array( $responsible );
				}
				foreach ( $responsible as $item ) {
					$user = get_user_by( 'id', $item );
					if ( $user ) {
						$responsible_ids[]   = $item;
						$responsible_names[] = $user->display_name;
					}
				}
				if ( $observers ) {
					if ( ! is_array( $observers ) ) {
						$observers = array( $observers );
					}
					foreach ( $observers as $item ) {
						$user = get_user_by( 'id', $item );
						if ( $user ) {
							$observers_ids[]   = $item;
							$observers_names[] = $user->display_name;
						}
					}
				}
				wp_set_post_terms( $project_id, array(), 'tags', false );
				if ( $tags ) {
					if ( is_array( $tags ) ) {
						foreach ( $tags as $tag ) {
							$tag = (int) $tag;
							wp_set_post_terms( $project_id, array( $tag ), 'tags', true );
						}
					} else {
						$tags = (int) $tags;
						wp_set_post_terms( $project_id, array( $tags ), 'tags', true );
					}
				}
				$worksection_id = carbon_get_user_meta( $responsible_ids[0], 'worksection_id' );
				carbon_set_post_meta( $project_id, 'worksection_user_to_id', $worksection_id );
				carbon_set_post_meta( $project_id, 'project_users_to_id', implode( ',', $responsible_ids ) );
				carbon_set_post_meta( $project_id, 'project_users_to_name', implode( ',', $responsible_names ) );
				carbon_set_post_meta( $project_id, 'project_users_observer_id', implode( ',', $observers_ids ) );
				carbon_set_post_meta( $project_id, 'project_users_observer_name', implode( ',', $observers_names ) );
				carbon_set_post_meta( $project_id, 'project_user_from_id', $user_id );
				carbon_set_post_meta( $project_id, 'project_user_from_name', get_user_by( 'id', $user_id )->display_name );
				$res['url']  = get_the_permalink( $project_id );
				$res['type'] = 'success';
				if ( $responsible_ids && ! $edit_project_id ) {
					foreach ( $responsible_ids as $_user_id ) {
						if ( carbon_get_user_meta( $_user_id, 'project_notification' ) ) {
							send_notification( $_user_id, $project_id );
						}
						create_notification( $project_id, $comment_id, 'Новий проєкт ' . $title );
					}
				}
			} else {
				$res['type'] = 'error';
				$res['msg']  = $project_id->get_error_message();
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Заповніть обовʼязкові поля';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка доступу';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_get_data_users', 'get_data_users' );
add_action( 'wp_ajax_get_data_users', 'get_data_users' );
function get_data_users() {
	$res   = array();
	$users = get_users();
	if ( $users ) {
		foreach ( $users as $user ) {
			$ID         = $user->ID;
			$avatar     = carbon_get_user_meta( $ID, 'avatar' );
			$avatar     = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $ID );
			$nick_name  = esc_html( $user->display_name );
			$last_name  = $user->last_name;
			$first_name = $user->first_name;
			$name       = $last_name;
			if ( $first_name ) {
				$name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
			}
			$res[ $ID ] = array(
				'ID'        => $ID,
				'nick_name' => $nick_name,
				'name'      => $name,
				'src'       => $avatar,
			);
		}
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_starting_project', 'starting_project' );
add_action( 'wp_ajax_starting_project', 'starting_project' );
function starting_project() {
	$res        = array();
	$user_id    = get_current_user_id();
	$project_id = $_POST['project_id'] ?? '';
	if ( $project_id && get_post( $project_id ) && $user_id ) {
		carbon_set_user_meta( $user_id, 'current_project', $project_id );
		$post_status = get_post_status( $project_id );
		if ( $post_status != 'publish' ) {
			$post_data  = array(
				'post_status' => 'publish',
				'ID'          => $project_id,
			);
			$project_id = wp_update_post( $post_data, true );
			if ( $project_id && ! is_wp_error( $project_id ) ) {
				$res['type'] = 'success';

			} else {
				$res['type'] = 'error';
				$res['msg']  = $project_id->get_error_message();
			}
		}
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_save_user_time', 'save_user_time' );
add_action( 'wp_ajax_save_user_time', 'save_user_time' );
function save_user_time() {
	$res              = array();
	$stopwatches      = $_POST['stopwatches'] ?? '';
	$work_times       = $_POST['work_times'] ?? '';
	$start            = $_POST['start'] ?? '';
	$finish           = $_POST['finish'] ?? '';
	$unix             = $_POST['unix'] ?? '';
	$status           = $_POST['status'] ?? '0';
	$costs_sum        = $_POST['costs_sum'] ?? '';
	$costs_sum_hour   = $_POST['costs_sum_hour'] ?? '';
	$pause_time       = $_POST['pause_time'] ?? '';
	$pause_time_hour  = $_POST['pause_time_hour'] ?? '';
	$date             = $_POST['date'] ?? '';
	$get_result_modal = $_POST['get_result_modal'] ?? '0';
	$user_id          = get_current_user_id();
	if ( $user_id && $date && $work_times ) {
		$cost_id   = get_cost_id( array(
			'user_id' => $user_id,
			'date'    => $date,
		) );
		$post_data = array(
			'post_type'   => 'costs',
			'post_title'  => $date,
			'post_status' => 'publish',
		);
		if ( $cost_id ) {
			$post_data['ID'] = $cost_id;
		} else {
			$post_data['post_author'] = $user_id;
		}
		$id = $cost_id ? wp_update_post( $post_data, true ) : wp_insert_post( $post_data, true );
		if ( $id && ! is_wp_error( $id ) ) {
			$costs_list = array();
			$time_test  = 0;
			foreach ( $work_times as $stopwatch ) {
				$s = (int) $stopwatch['start'];
				$f = (int) $stopwatch['finish'];
				if ( $f === 0 ) {
					if ( $time_test === 0 ) {
						$time_test = $f;
					} else {
						$res['ID']   = $id;
						$res['post'] = $_POST;
						echo json_encode( $res );
						die();
					}
				}
				$costs_list[] = array(
					'time_start'  => round( $s / 1000 ),
					'time_finish' => round( $f / 1000 )
				);
			}
			$time_str       = $costs_sum_hour['hours'] . ":" . $costs_sum_hour['minutes'] . ":" . $costs_sum_hour['seconds'];
			$pause_time_str = $pause_time_hour['hours'] . ":" . $pause_time_hour['minutes'] . ":" . $pause_time_hour['seconds'];
			carbon_set_post_meta( $id, 'costs_date', $date );
			carbon_set_post_meta( $id, 'costs_start', $start );
			carbon_set_post_meta( $id, 'costs_finish', $finish );
			carbon_set_post_meta( $id, 'costs_sum_hour', $time_str );
			carbon_set_post_meta( $id, 'costs_sum', $costs_sum );
			carbon_set_post_meta( $id, 'costs_sum_hour_pause', $pause_time_str );
			carbon_set_post_meta( $id, 'costs_sum_pause', $pause_time );
			carbon_set_post_meta( $id, 'costs_status', $status );
			carbon_set_post_meta( $id, 'costs_list', $costs_list );
			carbon_set_post_meta( $id, 'costs_data', json_encode( $work_times ) );
			$pd   = carbon_get_post_meta( $id, 'post_data' );
			$time = time();
			$pd   .= PHP_EOL .
			         '____________________________________________________________________________________________________________________________________________________________________________________' .
			         date( 'd-m-Y H:i:s', $time ) .
			         '____________________________________________________________________________________________________________________________________________________________________________________' .
			         json_encode( $_POST );
			carbon_set_post_meta( $id, 'post_data', $pd );
			if ( $stopwatches ) {
				carbon_set_post_meta( $id, 'pauses', json_encode( $stopwatches ) );
			}
			$res['ID'] = $id;
			if ( $get_result_modal == '1' ) {
				ob_start();
				the_timer_modal( array(
					'user_id' => $user_id,
					'date'    => $date,
				) );
				$res['timer_modal_html'] = ob_get_clean();
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = $id->get_error_message();
		}
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_get_user_time', 'get_user_time' );
add_action( 'wp_ajax_get_user_time', 'get_user_time' );
function get_user_time() {
	$res     = array();
	$user_id = get_current_user_id();
	$date    = $_POST['date'] ?? '';
	if ( $user_id && $date ) {
		$cost_id        = get_cost_id( array(
			'user_id' => $user_id,
			'date'    => $date,
		) );
		$res['cost_id'] = $cost_id;
		$res['user_id'] = $user_id;
		if ( $cost_id ) {
			$res['pauses']                = carbon_get_post_meta( $cost_id, 'pauses' );
			$res['costs_data']            = carbon_get_post_meta( $cost_id, 'costs_data' );
			$res['costs_status']          = (int) carbon_get_post_meta( $cost_id, 'costs_status' );
			$res['costs_start']           = carbon_get_post_meta( $cost_id, 'costs_start' );
			$res['costs_finish']          = carbon_get_post_meta( $cost_id, 'costs_finish' );
			$res['costs_sum_hour']        = carbon_get_post_meta( $cost_id, 'costs_sum_hour' );
			$res['costs_sum']             = carbon_get_post_meta( $cost_id, 'costs_sum' );
			$res['costs_sum_hour_pause']  = carbon_get_post_meta( $cost_id, 'costs_sum_hour_pause' );
			$res['costs_sum_pause']       = carbon_get_post_meta( $cost_id, 'costs_sum_pause' );
			$res['costs_sum_hour_change'] = carbon_get_post_meta( $cost_id, 'costs_sum_hour_change' );
			carbon_set_post_meta( $cost_id, 'res_data', json_encode( $res ) );
		}
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_create_event', 'create_event' );
add_action( 'wp_ajax_create_event', 'create_event' );
function create_event() {
	$res     = array();
	$user_id = get_current_user_id();
	if ( $user_id && is_current_user_admin() ) {
		$title        = $_POST['title'] ?? '';
		$text         = $_POST['text'] ?? '';
		$question     = $_POST['question'] ?? '';
		$answers      = $_POST['answer'] ?? '';
		$is_anonymous = $_POST['voting'] ?? '';
		$type         = $_POST['type'] ?? 'radio';
		if ( $title && $text ) {
			$post_data = array(
				'post_type'    => 'events',
				'post_title'   => $title,
				'post_status'  => 'publish',
				'post_content' => $text,
				'post_author'  => $user_id
			);
			$post_id   = wp_insert_post( $post_data, true );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				if ( $question && $answers ) {
					$answers_array = array();
					if ( is_array( $answers ) ) {
						foreach ( $answers as $answer ) {
							if ( $answer && $answer != '' ) {
								$answers_array[] = array(
									'answer' => $answer
								);
							}
						}
					} else {
						$answers_array = array(
							array(
								'answer' => $answers
							)
						);
					}
					carbon_set_post_meta( $post_id, 'event_question', $question );
					carbon_set_post_meta( $post_id, 'event_answers', $answers_array );
					carbon_set_post_meta( $post_id, 'event_anonymous', $is_anonymous == 'anonymous' );
					carbon_set_post_meta( $post_id, 'event_multiple', $type == 'checkbox' );
					ob_start();
					the_events_section( false );
					$res['events_html'] = ob_get_clean();
				}
			} else {
				$res['type'] = 'error';
				$res['msg']  = $post_id->get_error_message();
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Заповніть хоча б назву і події і її опис';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка доступу';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_save_event_result', 'save_event_result' );
add_action( 'wp_ajax_save_event_result', 'save_event_result' );
function save_event_result() {
	$res       = array();
	$user_id   = get_current_user_id();
	$id        = $_POST['id'] ?? '';
	$answers   = $_POST['answers'] ?? '';
	$is_vote   = $_POST['is_vote'] ?? '0';
	$show_test = $_POST['show_test'] ?? '0';
	if ( $user_id && $id && get_post( $id ) ) {
		$get_user_result = get_user_event_result_id( $id, $user_id );
		if ( $get_user_result ) {
			carbon_set_post_meta( $get_user_result, 'event_acquainted', $is_vote == '0' );
			if ( $answers ) {
				carbon_set_post_meta( $get_user_result, 'event_result_answers', implode( ',', $answers ) );
			}
			ob_start();
			if ( $show_test ) {
				the_event( $id, true );
				$res['event_html'] = ob_get_clean();
				$res['event_id']   = $id;
			} else {
				the_events_section( false );
				$res['events_html'] = ob_get_clean();
			}

		} else {
			$post_data = array(
				'post_type'   => 'event_results',
				'post_title'  => get_the_title( $id ),
				'post_status' => 'publish',
				'post_author' => $user_id
			);
			$post_id   = wp_insert_post( $post_data, true );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				carbon_set_post_meta( $post_id, 'event_id', $id );
				carbon_set_post_meta( $post_id, 'event_acquainted', $is_vote == '0' );
				if ( $answers ) {
					carbon_set_post_meta( $post_id, 'event_result_answers', implode( ',', $answers ) );
				}
				ob_start();
				if ( $show_test ) {
					the_event( $id, true );
					$res['event_html'] = ob_get_clean();
					$res['event_id']   = $id;
				} else {
					the_events_section( false );
					$res['events_html'] = ob_get_clean();
				}
			} else {
				$res['type'] = 'error';
				$res['msg']  = $post_id->get_error_message();
			}
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_reading_discussion', 'reading_discussion' );
add_action( 'wp_ajax_reading_discussion', 'reading_discussion' );
function reading_discussion() {
	$id      = $_POST['id'] ?? '';
	$user_id = get_current_user_id();
	if ( $user_id && $id && get_post( $id ) ) {
		$users_read = carbon_get_post_meta( $id, 'discussion_read_users' );
		if ( $users_read ) {
			$users_read = explode( ',', $users_read );
		} else {
			$users_read = array();
		}
		$users_read[] = $user_id;
		$users_read   = array_unique( $users_read );
		carbon_set_post_meta( $id, 'discussion_read_users', implode( ',', $users_read ) );
		$notification_id = get_user_notification_by_comment_id( $id, $user_id );
		if ( $notification_id ) {
			wp_delete_post( $notification_id );
		}
		echo json_encode( $users_read );
	}
	die();
}

add_action( 'wp_ajax_nopriv_get_user_notifications', 'get_user_notifications' );
add_action( 'wp_ajax_get_user_notifications', 'get_user_notifications' );
function get_user_notifications() {
	$user_id = get_current_user_id();
	$res     = array( 'count' => 0 );
	if ( $user_id ) {
		$res = get_user_notification( $user_id );
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_archive_projects', 'archive_projects' );
add_action( 'wp_ajax_archive_projects', 'archive_projects' );
function archive_projects() {
	$cockie   = $_COOKIE['selected_project'] ?? '';
	$query    = $_POST['query'] ?? '';
	$errors   = array();
	$is_admin = is_current_user_admin();
	if ( $cockie && $is_admin ) {
		$cockie = explode( ',', $cockie );
		if ( count( $cockie ) > 0 ) {
			$args = array(
				'post_type'      => 'projects',
				'posts_per_page' => - 1,
				'post_status'    => array( 'publish', 'pending' ),
			);
			if ( $query ) {
				$query                  = stripcslashes( $query );
				$query                  = json_decode( $query, true );
				$args                   = array_merge( $query, $args );
				$args['posts_per_page'] = - 1;
			}
			if ( ! in_array( '-1', $cockie ) ) {
				$args['post__in'] = $cockie;
			}
			$query = new WP_Query( $args );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$id              = get_the_ID();
					$post_data       = array(
						'post_status' => 'archive',
					);
					$post_data['ID'] = $id;
					$project_id      = wp_update_post( $post_data, true );
					if ( is_wp_error( $project_id ) ) {
						$err      = $project_id->get_error_message();
						$errors[] = 'Проєкт ' . $project_id . '[' . $err . ']';
					}
				}
			}
			wp_reset_postdata();
			wp_reset_query();
		}

	}
	$projects_url = get_post_type_archive_link( 'projects' );
	echo json_encode( array(
		'url'    => $projects_url,
		'errors' => implode( '; ', $errors )
	) );
	die();
}

add_action( 'wp_ajax_nopriv_change_user_time', 'change_user_time' );
add_action( 'wp_ajax_change_user_time', 'change_user_time' );
function change_user_time() {
	$res     = array();
	$id      = $_POST['id'] ?? '';
	$time    = $_POST['time'] ?? '';
	$text    = $_POST['text'] ?? '';
	$user_id = get_current_user_id();
	if ( $id && get_post( $id ) && $user_id && $time ) {
		$author = get_post_author_id( $id );
		if ( (int) $author === $user_id ) {
			carbon_set_post_meta( $id, 'costs_sum_hour_change', $time );
			carbon_set_post_meta( $id, 'costs_change_text', $text );
			$_date              = carbon_get_post_meta( $id, 'costs_date' );
			$_sum_hour          = carbon_get_post_meta( $id, 'costs_sum_hour' );
			$str                = " (з $_sum_hour до $time)";
			$res['time']        = $time;
			$res['change_date'] = date( 'd.m.Y', time() );
			$res['type']        = 'success';
			$res['msg']         = 'Зміненно ' . date( 'd.m.Y', time() );
			$post_data          = array(
				'post_type'    => 'discussion',
				'post_title'   => 'Заявка на зміну часу ' . $_date . $str,
				'post_status'  => 'publish',
				'post_content' => $text,
				'post_author'  => $user_id
			);
			$comment_id         = wp_insert_post( $post_data, true );
			if ( ! is_wp_error( $comment_id ) ) {
				carbon_set_post_meta( $comment_id, 'discussion_project_id', $id );
				create_notification( $id, $comment_id, $text );
			} else {
				$res['type'] = 'error';
				$res['msg']  = $comment_id->get_error_message();
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Помилка доступу';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_change_user_data', 'change_user_data' );
add_action( 'wp_ajax_change_user_data', 'change_user_data' );
function change_user_data() {
	$res         = array();
	$result      = array();
	$change_data = array();
	$user_id     = get_current_user_id();
	if ( $user_id ) {
		$firstname = $_POST['user_firstname'] ?? '';
		$lastname  = $_POST['user_lastname'] ?? '';
		$phone     = $_POST['phone'] ?? '';
		$email     = $_POST['email'] ?? '';
		$birthday  = $_POST['birthday'] ?? '';
		$position  = $_POST['position'] ?? '';
		$user      = get_user_by( 'id', $user_id );
		if ( $user ) {
			$_name          = $user->display_name;
			$_user_email    = $user->user_email;
			$_user_name     = $user->user_firstname;
			$_user_lastname = $user->user_lastname;
			$_position      = carbon_get_user_meta( $user_id, 'position' );
			$_user_tel      = carbon_get_user_meta( $user_id, 'user_tel' );
			$_birthday      = carbon_get_user_meta( $user_id, 'birthday' );
			$_avatar        = carbon_get_user_meta( $user_id, 'avatar' );
			$args           = array(
				'ID' => $user_id,
			);
			if ( $firstname != $_user_name && $firstname != '' ) {
				$args['first_name'] = $firstname;
				wp_update_user( $args );
				$result[]                      = 'Імя змінено';
				$change_data['user_firstname'] = $firstname;
				$change_data['name']           = $user->display_name;
			}
			if ( $lastname != $_user_lastname && $lastname != '' ) {
				$args['last_name'] = $lastname;
				wp_update_user( $args );
				$result[]                  = 'Прізвище змінено';
				$change_data['first_name'] = $firstname;
				$change_data['name']       = $user->display_name;
			}
			$first_name = get_user_meta( $user->ID, 'first_name', true );
			$last_name  = get_user_meta( $user->ID, 'last_name', true );
			$full_name  = trim( $last_name . ' ' . $first_name );
			if ( ! empty( $full_name ) && ( $user->data->display_name != $full_name ) ) {
				$userdata = array(
					'ID'           => $user_id,
					'display_name' => $full_name,
				);

				wp_update_user( $userdata );
				$change_data['name'] = $user->display_name;
			}
			if ( $email != $_user_email && $email != '' ) {
				if ( email_exists( $email ) ) {
					$result[] = 'Email вже занятий';
				} else {
					$result[]           = 'Email змінений';
					$args['user_email'] = $email;
					wp_update_user( $args );
					$change_data['email'] = $email;
				}
			}
			if ( $phone != $_user_tel && $phone != '' ) {
				carbon_set_user_meta( $user_id, 'user_tel', $phone );
				$result[]                = 'Телефон змінений';
				$change_data['user_tel'] = $phone;
			}
			if ( $birthday != $_birthday && $birthday != '' ) {
				carbon_set_user_meta( $user_id, 'birthday', $birthday );
				$result[]                = 'День народження змінено';
				$change_data['birthday'] = $birthday;
				create_cron_birthday( $user_id );
			}
			if ( $position != $_position && $position != '' ) {
				carbon_set_user_meta( $user_id, 'position', $position );
				$result[]                = 'Посаду змінено';
				$change_data['position'] = $position;
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Помилка';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка';
	}
	$res['msg']         = $res['msg'] . '' . implode( ', ', $result );
	$res['change_data'] = $change_data;
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_change_user', 'change_user' );
add_action( 'wp_ajax_change_user', 'change_user' );
function change_user() {
	$res         = array();
	$result      = array();
	$change_data = array();
	$user_admin  = is_current_user_admin();
	$user_id     = $_POST['user_id'] ?? '';
	if ( $user_admin && $user_id ) {
		$firstname              = $_POST['first_name'] ?? '';
		$lastname               = $_POST['last_name'] ?? '';
		$phone                  = $_POST['tel'] ?? '';
		$email                  = $_POST['email'] ?? '';
		$birthday               = $_POST['birthday'] ?? '';
		$position               = $_POST['position'] ?? '';
		$worksection_id         = $_POST['worksection_id'] ?? '';
		$user                   = get_user_by( 'id', $user_id );
		$change_data['user_id'] = $user_id;
		if ( $user ) {
			$_name           = $user->display_name;
			$_user_email     = $user->user_email;
			$_user_name      = $user->user_firstname;
			$_user_lastname  = $user->user_lastname;
			$_position       = carbon_get_user_meta( $user_id, 'position' );
			$_user_tel       = carbon_get_user_meta( $user_id, 'user_tel' );
			$_birthday       = carbon_get_user_meta( $user_id, 'birthday' );
			$_worksection_id = carbon_get_user_meta( $user_id, 'worksection_id' );
			$args            = array(
				'ID' => $user_id,
			);
			if ( $firstname != $_user_name && $firstname != '' ) {
				$args['first_name'] = $firstname;
				wp_update_user( $args );
				$result[]                      = 'Імя змінено';
				$change_data['user_firstname'] = $firstname;
				$change_data['name']           = $user->display_name;
			}
			if ( $lastname != $_user_lastname && $lastname != '' ) {
				$args['last_name'] = $lastname;
				wp_update_user( $args );
				$result[]                  = 'Прізвище змінено';
				$change_data['first_name'] = $firstname;
				$change_data['name']       = $user->display_name;
			}
			$first_name = get_user_meta( $user->ID, 'first_name', true );
			$last_name  = get_user_meta( $user->ID, 'last_name', true );
			$full_name  = trim( $last_name . ' ' . $first_name );
			if ( ! empty( $full_name ) && ( $user->data->display_name != $full_name ) ) {
				$userdata = array(
					'ID'           => $user_id,
					'display_name' => $full_name,
				);

				wp_update_user( $userdata );
				$change_data['name'] = $user->display_name;
			}
			if ( $email != $_user_email && $email != '' ) {
				if ( email_exists( $email ) ) {
					$result[] = 'Email вже занятий';
				} else {
					$result[]           = 'Email змінений';
					$args['user_email'] = $email;
					wp_update_user( $args );
					$change_data['email'] = $email;
				}
			}
			if ( $phone != $_user_tel && $phone != '' ) {
				carbon_set_user_meta( $user_id, 'user_tel', $phone );
				$result[]                = 'Телефон змінений';
				$change_data['user_tel'] = $phone;
			}
			if ( $birthday != $_birthday && $birthday != '' ) {
				carbon_set_user_meta( $user_id, 'birthday', $birthday );
				$result[]                = 'День народження змінено';
				$change_data['birthday'] = $birthday;
				create_cron_birthday( $user_id );
			}
			if ( $position != $_position && $position != '' ) {
				carbon_set_user_meta( $user_id, 'position', $position );
				$result[]                = 'Посаду змінено';
				$change_data['position'] = $position;
			}
			if ( $worksection_id != $_worksection_id && $worksection_id != '' ) {
				carbon_set_user_meta( $user_id, 'worksection_id', $worksection_id );
				$result[]                      = 'Worksection_id змінено';
				$change_data['worksection_id'] = $worksection_id;
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Помилка';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка';
	}
	$res['msg']         = $res['msg'] . '' . implode( ', ', $result );
	$res['change_data'] = $change_data;
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_change_user_notifications', 'change_user_notifications' );
add_action( 'wp_ajax_change_user_notifications', 'change_user_notifications' );
function change_user_notifications() {
	$user_id = get_current_user_id();
	$res     = array();
	if ( $user_id ) {
		$project_notification  = $_POST['project_notification'] ?? '';
		$comment_notification  = $_POST['comment_notification'] ?? '';
		$birthday_notification = $_POST['birthday_notification'] ?? '';
		$telegram_notification = $_POST['telegram_notification'] ?? '';
		$email_notification    = $_POST['email_notification'] ?? '';
		carbon_set_user_meta( $user_id, 'project_notification', $project_notification == 'yes' );
		carbon_set_user_meta( $user_id, 'comment_notification', $comment_notification == 'yes' );
		carbon_set_user_meta( $user_id, 'birthday_notification', $birthday_notification == 'yes' );
		carbon_set_user_meta( $user_id, 'telegram_notification', $telegram_notification == 'yes' );
		carbon_set_user_meta( $user_id, 'email_notification', $email_notification == 'yes' );
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_remove_avatar', 'remove_avatar' );
add_action( 'wp_ajax_remove_avatar', 'remove_avatar' );
function remove_avatar() {
	$user_id = get_current_user_id();
	$res     = array();
	if ( $user_id ) {
		$res['avatar'] = remove_user_avatar( $user_id );
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка';
	}
	echo json_encode( $res );
	die();
}

function remove_user_avatar( $user_id ) {
	if ( $user_id ) {
		$avatar = carbon_get_user_meta( $user_id, 'avatar' );
		if ( $avatar ) {
			wp_delete_attachment( $avatar );
			carbon_set_user_meta( $user_id, 'avatar', '' );
		}
		$avatar = carbon_get_user_meta( $user_id, 'avatar' );

		return $avatar ? _u( $avatar, 1 ) : get_avatar_url( $user_id );
	}

	return false;
}

add_action( 'wp_ajax_nopriv_change_user_avatar', 'change_user_avatar' );
add_action( 'wp_ajax_change_user_avatar', 'change_user_avatar' );
function change_user_avatar() {
	$user_id = get_current_user_id();
	$res     = array();
	if ( $user_id ) {
		$res['remove_avatar'] = remove_user_avatar( $user_id );
		$files                = $_FILES["upfile"];
		$arr                  = array();
		$res['$files']        = $files;
		foreach ( $files['name'] as $key => $value ) {
			if ( $files['name'][ $key ] ) {
				$file   = array(
					'name'     => $files['name'][ $key ],
					'type'     => $files['type'][ $key ],
					'tmp_name' => $files['tmp_name'][ $key ],
					'error'    => $files['error'][ $key ],
					'size'     => $files['size'][ $key ]
				);
				$_FILES = array( "file" => $file );
				foreach ( $_FILES as $file => $array ) {
					$arr[] = my_handle_attachment( $file );
				}
			}
		}
		carbon_set_user_meta( $user_id, 'avatar', $arr[0] );
		$res['avatar'] = _u( $arr[0], 1 );
		$res['type']   = 'success';
	} else {
		$res['type']      = 'error';
		$res['msg']       = 'Авторизуйтесь';
		$res['is_reload'] = 'true';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_get_projects_list', 'get_projects_list' );
add_action( 'wp_ajax_get_projects_list', 'get_projects_list' );
function get_projects_list() {
	$string  = $_POST['string'] ?? '';
	$exclude = $_POST['exclude'] ?? '';
	$res     = array();
	if ( $string ) {
		$args = array(
			'post_type'      => 'projects',
			'post_status'    => array( 'publish', 'pending', 'archive' ),
			'posts_per_page' => - 1,
			's'              => $string
		);
		if ( $exclude ) {
			$args['post__not_in'] = explode( ',', $exclude );
		}
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$_id    = get_the_ID();
				$_title = get_the_title();
				$res[]  = array(
					'name' => $_title,
					'val'  => $_id,
				);
			}
		} else {
			$res[] = array(
				'name' => "Не знайдено",
			);
		}
		wp_reset_postdata();
		wp_reset_query();
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_get_timers_html', 'get_timers_html' );
add_action( 'wp_ajax_get_timers_html', 'get_timers_html' );
function get_timers_html() {
	if ( is_current_user_admin() ) {
		the_timer_list();
	}
	die();
}

add_action( 'wp_ajax_nopriv_create_new_user', 'create_new_user' );
add_action( 'wp_ajax_create_new_user', 'create_new_user' );
function create_new_user() {
	$res            = array();
	$last_name      = $_POST['last_name'] ?? '';
	$first_name     = $_POST['first_name'] ?? '';
	$email          = $_POST['email'] ?? '';
	$tel            = $_POST['tel'] ?? '';
	$position       = $_POST['position'] ?? '';
	$birthday       = $_POST['birthday'] ?? '';
	$worksection_id = $_POST['worksection_id'] ?? '';
	if ( is_current_user_admin() ) {
		$test = $first_name && $last_name && $email;
		if ( $test ) {
			if ( $user_id = email_exists( $email ) ) {
				$res['type'] = 'error';
				$res['msg']  = 'Email вже зайнятий';
			} else {
				$password = wp_generate_password( 16, true );
				$user_id  = wp_create_user( $email, $password, $email );
				if ( $user_id ) {
					$full_name            = trim( $last_name . ' ' . $first_name );
					$args                 = array(
						'ID' => $user_id,
					);
					$args['last_name']    = $last_name;
					$args['first_name']   = $first_name;
					$args['display_name'] = $full_name;
					wp_update_user( $args );
					if ( $tel ) {
						carbon_set_user_meta( $user_id, 'user_tel', $tel );
					}
					if ( $position ) {
						carbon_set_user_meta( $user_id, 'position', $position );
					}
					if ( $birthday ) {
						carbon_set_user_meta( $user_id, 'birthday', $birthday );
						create_cron_birthday( $user_id );
					}
					carbon_set_user_meta( $user_id, 'worksection_user_to_id', $user_id );
					carbon_set_user_meta( $user_id, 'worksection_id', $worksection_id );
					$url            = site_url();
					$res['type']    = 'success';
					$res['user_id'] = $user_id;
					$res['msg']     = "Користувача $first_name $last_name було створено!";
					$m              = "<h1>Шановний $first_name, вітаємо вас у нас в команді!</h1>";
					$m              .= "<br><br>";
					$m              .= "Для авторизації перейдіть по <a target='_blank' href='$url'>посиланню</a><hr>";
					$m              .= "Email: <strong>$email</strong><br>";
					$m              .= "Пароль: <strong>$password</strong><hr>";
					$m              .= "<em>Просимо вас дозаповнити свої відсутні дані, якщо такі є.</em>";
					send_message( $m, $email, "Доступи до Web-Mosaica CRM" );
				} else {
					$res['type'] = 'error';
					$res['msg']  = 'Помилка';
				}
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Необхідно заповнити обовязкові поля';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка доступу';
	}
	echo json_encode( $res );
	die();
}

add_action( 'wp_ajax_nopriv_dismiss_user', 'dismiss_user' );
add_action( 'wp_ajax_dismiss_user', 'dismiss_user' );
function dismiss_user() {
	$res = array();
	if ( is_current_user_admin() ) {
		$user_id = $_POST['userID'] ?? '';
		if ( $user_id ) {
			if ( $user = get_user_by( 'id', $user_id ) ) {
				carbon_set_user_meta( $user_id, 'fired', true );
				$res['type']    = 'success';
				$res['user_id'] = $user_id;
			} else {
				$res['type'] = 'error';
				$res['msg']  = 'Користувача не знайдено';
			}
		} else {
			$res['type'] = 'error';
			$res['msg']  = 'Помилка';
		}
	} else {
		$res['type'] = 'error';
		$res['msg']  = 'Помилка доступу';
	}
	echo json_encode( $res );
	die();
}

function get_cost_id( $arr = array() ) {
	$res     = 0;
	$user_id = $arr['user_id'] ?? false;
	$date    = $arr['date'] ?? false;
	if ( $user_id && $date ) {
		$args  = array(
			'post_type'      => 'costs',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'author__in'     => array( $user_id ),
			'meta_query'     => array(
				array(
					'key'   => '_costs_date',
					'value' => $date,
				)
			)
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
	}

	return $res;
}

function create_comment( $comment_data ) {
	date_default_timezone_set( "Europe/Kiev" );
	$user_id             = get_current_user_id();
	$project_id          = $comment_data['id'] ?? 0;
	$hush_text           = mb_strtolower( trim( $comment_data['text'] ), 'UTF-8' );
	$hush_text           = strip_tags( $hush_text );
	$text_test           = mb_strlen( $hush_text, 'UTF-8' ) > 0;
	$hush                = md5( $user_id . $project_id . $hush_text );
	$project_comment_ids = carbon_get_post_meta( $project_id, 'project_comment_ids' );
	if ( $project_comment_ids ) {
		$project_comment_ids = explode( ',', $project_comment_ids );
	} else {
		$project_comment_ids = array();
	}
	$post_data  = array(
		'post_type'    => 'discussion',
		'post_title'   => 'Comment',
		'post_status'  => 'publish',
		'post_content' => $comment_data['text'],
		'post_author'  => $user_id
	);
	$comment_id = wp_insert_post( $post_data, true );
	if ( ! is_wp_error( $comment_id ) ) {
		carbon_set_post_meta( $comment_id, 'discussion_project_hush', $hush );
		carbon_set_post_meta( $comment_id, 'discussion_project_id', $project_id );
		carbon_set_post_meta( $comment_id, 'discussion_is_service', $comment_data['is_service'] ?? false );
		$project_comment_ids[] = $comment_id;
		carbon_set_post_meta( $project_id, 'project_comment_ids', implode( ',', $project_comment_ids ) );

		return $comment_id;
	} else {
		return 0;
	}
}