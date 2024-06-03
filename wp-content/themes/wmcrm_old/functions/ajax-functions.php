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
	$res               = array();
	$user_id           = get_current_user_id();
	$text              = $_POST['text'] ?? '';
	$project_id        = $_POST['project_id'] ?? '';
	$update_comment_id = $_POST['comment_id'] ?? '';
	$hush_text         = mb_strtolower( trim( $text ), 'UTF-8' );
	$hush_text         = strip_tags( $hush_text );
	$text_test         = mb_strlen( $hush_text, 'UTF-8' ) > 0;
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
			$text1         = get_text_with_users( $text );
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
			} else {
				$comment_id = wp_insert_post( $post_data, true );
			}
			if ( ! is_wp_error( $comment_id ) ) {
				carbon_set_post_meta( $comment_id, 'discussion_project_hush', $hush );
				carbon_set_post_meta( $comment_id, 'discussion_project_id', $project_id );
				if ( ! $update_comment_id ) {
					$project_comment_ids[] = $comment_id;
					carbon_set_post_meta( $project_id, 'project_comment_ids', implode( ',', $project_comment_ids ) );
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
		if ( wp_delete_post( $comment_id ) ) {
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
					$comment_id = create_comment( array(
						'text'       => $status == 'publish' ? 'Проєкт відкрито' : 'Проєкт закрито',
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
		if ( $title && $responsible ) {
			$responsible_ids   = array();
			$responsible_names = array();
			$text              = $text ? get_text_with_users( $text ) : '';
			$post_data         = array(
				'post_type'    => 'projects',
				'post_title'   => $title,
				'post_status'  => 'publish',
				'post_content' => $text,
				'post_author'  => $user_id
			);
			$project_id        = 0;
			if ( $edit_project_id && get_post( $edit_project_id ) ) {
				$post_data['ID'] = $edit_project_id;
				$project_id      = wp_update_post( $post_data, true );
			} else {
				$project_id = wp_insert_post( $post_data, true );
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
				carbon_set_post_meta( $project_id, 'project_user_from_id', $user_id );
				carbon_set_post_meta( $project_id, 'project_user_from_name', get_user_by( 'id', $user_id )->display_name );
				$res['url']  = get_the_permalink( $project_id );
				$res['type'] = 'success';
				if ( $responsible_ids ) {
					foreach ( $responsible_ids as $_user_id ) {
						send_notification( $_user_id, $project_id );
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