<?php

function absences_action() {
	$res             = '';
	$action          = $_GET['action'] ?? '';
	$id              = $_GET['id'] ?? '';
	$current_user_id = get_current_user_id();
	if ( $action == 'confirm_absences' || $action == 'remove_absences' && $id && get_post( $id ) ) {
		$id = (int) $id;
		if ( $action == 'confirm_absences' && get_post_status( $id ) != 'publish' && is_current_user_admin() ) {
			$my_post = array(
				'ID'          => $id,
				'post_status' => 'publish',
			);
			$id      = wp_update_post( $my_post, true );
			if ( ! is_wp_error( $id ) ) {
				$res     = '<div class="admin-notification">Відгул підтверджений <a href="#" class="close-notice">Прочитано ☑️</a></div>';
				$user_id = get_post_author_id( $id );
				if ( $user_id ) {
					if ( ! carbon_get_user_meta( $user_id, 'fired' ) ) {
						$start_date  = carbon_get_post_meta( $id, 'absences_start_date' );
						$finish_date = carbon_get_post_meta( $id, 'absences_finish_date' );
						$reasons     = get_the_terms( $id, 'reasons' );
						$user        = get_user_by( 'id', $user_id );
						$text        = 'Погоджено ';
						if ( $reasons ) {
							$text .= $reasons[0]->name;
							$text .= ' ';
						}
						if ( $start_date == $finish_date ) {
							$text .= 'дата відсутності ' . $start_date;
						} else {
							$text .= '(від ' . $start_date . ' до ' . $finish_date . ')';
						}
						if ( $user ) {
							if ( carbon_get_user_meta( $user_id, 'email_notification' ) ) {
								send_message( $text, $user->user_email, 'Погоджено відсутність' );
							}
							if ( carbon_get_user_meta( $user_id, 'telegram_notification' ) ) {
								if ( $telegram_id = carbon_get_user_meta( $user_id, 'telegram_id' ) ) {
									if ( is_working_hours() ) {
										send_telegram_message( $telegram_id, $text );
									} else {
										wp_schedule_single_event( get_next_work_timestamp(), 'send_telegram_message_action_hook', array(
											$telegram_id,
											$text,
											array(),
											false,
											'html'
										) );
									}
								}
							}
							$post_data = array(
								'post_type'   => 'notice',
								'post_title'  => $text,
								'post_status' => 'publish',
								'post_author' => $user_id
							);
							$notice_id = wp_insert_post( $post_data, true );
							if ( $notice_id && ! is_wp_error( $notice_id ) ) {
								carbon_set_post_meta( $notice_id, 'notice_type', 'notification' );
							}
						}
					}
				}
			}
		}
		if ( $action == 'remove_absences' ) {
			if ( $current_user_id && get_post_status( $id ) != 'publish' ) {
				$user_id = get_post_author_id( $id );
				if ( $current_user_id == $user_id || is_current_user_admin() ) {
					if ( wp_delete_post( $id ) ) {
						$res       = '<div class="admin-notification">Відгул видалено і непідтверджений <a href="#" class="close-notice">Прочитано ☑️</a></div>';
						$post_data = array(
							'post_type'   => 'notice',
							'post_title'  => 'Відгул видалено і непідтверджений',
							'post_status' => 'publish',
							'post_author' => $user_id
						);
						$notice_id = wp_insert_post( $post_data, true );
						if ( $notice_id && ! is_wp_error( $notice_id ) ) {
							carbon_set_post_meta( $notice_id, 'notice_type', 'warning' );
						}
					}
				}
			}

		}
	}

	return $res;
}

function change_user_time_event() {
	$action     = $_GET['action'] ?? '';
	$id         = $_GET['id'] ?? '';
	$comment_id = $_GET['comment_id'] ?? '';
	$is_admin   = is_current_user_admin();
	$user_id    = get_current_user_id();
	if ( $is_admin && carbon_get_user_meta( $user_id, 'super_admin' ) ) {
		$time = time();
		if ( $action === 'change_user_time' ) {
			if ( $id && get_post( $id ) ) {
				$costs_confirmed = carbon_get_post_meta( $id, 'costs_confirmed' );
				$costs_status    = carbon_get_post_meta( $id, 'costs_status' );
				$costs_text_list = carbon_get_post_meta( $id, 'costs_text_list' );
				if ( ! $costs_confirmed ) {
					carbon_set_post_meta( $id, 'costs_confirmed', date( 'd.m.Y', $time ) );
				}
				if ( $costs_status != 0 ) {
					carbon_set_post_meta( $id, 'costs_status', 0 );
				}
				$user = get_user_by( 'id', $user_id );
				$_temp = array(
					'text' => $user->display_name . ' погодив зміну і завершив робочий день',
					'unix' => time()
				);
				array_unshift( $costs_text_list, $_temp );
				carbon_set_post_meta( $id, 'costs_text_list', $costs_text_list );
			}
			$notification = get_user_notification_by_comment_id( $comment_id, $user_id );
			if ( $notification ) {
				wp_delete_post( $notification );
			}
		} elseif ( $action === 'remove_change_user_time' ) {
			if ( $comment_id ) {
				$notification = get_user_notification_by_comment_id( $comment_id, $user_id );
				wp_delete_post( $comment_id );
				if ( $notification ) {
					wp_delete_post( $notification );
				}
			}
		}
	}
}

function save_project_costs( $project_id, $args = array() ) {
	$time           = time();
	$old_project_id = $args['old_project_id'] ?? 0;
	$status         = $args['status'] ?? 1;
	$user_id        = get_current_user_id();
	if ( $project_id && get_post( $project_id ) && $user_id ) {
		$cost_id = get_project_cost_id( $project_id, $user_id );
		if ( $cost_id ) {
			$list = carbon_get_post_meta( $cost_id, 'project_costs_list' ) ?: array();
			if ( $list ) {
				if ( $list[ array_key_last( $list ) ]['finish'] == $list[ array_key_last( $list ) ]['start'] ) {
					$list[ array_key_last( $list ) ]['finish'] = $time;
				}
			}
			if ( $status === 1 ) {
				$list[] = array(
					'start'  => $time,
					'finish' => $time,
				);
			}
			carbon_set_post_meta( $cost_id, 'project_costs_list', $list );
		}
		if ( $old_project_id && get_post( $old_project_id ) ) {
			$cost_id = get_project_cost_id( $old_project_id, $user_id );
			if ( $cost_id ) {
				$list = carbon_get_post_meta( $cost_id, 'project_costs_list' ) ?: array();
				if ( $list ) {
					if ( $list[ array_key_last( $list ) ]['finish'] == $list[ array_key_last( $list ) ]['start'] ) {
						$list[ array_key_last( $list ) ]['finish'] = $time;
					}
					carbon_set_post_meta( $cost_id, 'project_costs_list', $list );
				}
			}
		}
	}
}

