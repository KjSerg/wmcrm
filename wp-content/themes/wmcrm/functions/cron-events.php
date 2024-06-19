<?php
add_action( 'create_notification_action_hook', 'create_notification', 10, 4 );

function create_notification( $project_id = 0, $comment_id = 0, $text = '', $users_ids = array() ) {
	$telegram_m                = '';
	$project_id                = $project_id ?? 0;
	$comment_id                = $comment_id ?? 0;
	$text                      = $text ?? '';
	$users_ids                 = $users_ids ?? array();
	$users                     = carbon_get_post_meta( $project_id, 'project_users_to_id' );
	$worksection_user_to_email = carbon_get_post_meta( $project_id, 'worksection_user_to_email' );
	$administrators            = get_administrators();
	$telegram_users            = array();
	$post_title                = 'notification';
	$post_type                 = get_post_type( $project_id );
	$comment_author_id         = $comment_id ? get_post_author_id( $comment_id ) : 0;
	if ( $comment_id && $project_id ) {
		$project_title = get_the_title( $project_id );
		$post_title    = 'Новий коментар до проєкта "' . $project_title . '"';
	}
	if ( $post_type == 'costs' ) {
		$costs_date = carbon_get_post_meta( $project_id, 'costs_date' );
		$post_title = 'Заявка зміни робочого часу ' . $costs_date;
		$author     = get_post_author_id( $project_id );
		$author     = get_user_by( 'id', $author );
		$post_title .= ' від ' . $author->display_name;
	}
	$m = '<h1>' . $post_title . '</h1> <hr><br>';
	$m .= $text;

	if ( $project_id ) {
		if ( $post_type == 'costs' ) {
			$link     = get_post_type_archive_link( 'discussion' );
			$sub_link = $link . '?id=' . $project_id . '&action=change_user_time';
			$m        .= '<hr><br> <a target="_blank" href="' . $sub_link . '">Підтвердити зміну</a>';
			$sub_link = $link . '?id=' . $project_id . '&comment_id=' . $comment_id . '&action=remove_change_user_time';
			$m        .= '<br> <a target="_blank" href="' . $sub_link . '">Відмінити зміну</a>';
		} else {
			$project_permalink = get_the_permalink( $project_id );
			$link              = $project_permalink;
			if ( $comment_id ) {
				$link .= '#comment-' . $comment_id;
			}
			$m .= '<hr><br> <a target="_blank" href="' . $link . '">Перейти до коментаря</a>';
			if ( $comment_author_id ) {
				$comment_author = get_user_by( 'id', $comment_author_id );
				$telegram_m     .= '<em>' . $comment_author->display_name . '</em>' . PHP_EOL;
			}
			$telegram_m .= "<a href='$link'>$post_title</a>" . PHP_EOL;

		}

	}
	$telegram_m .= PHP_EOL;
	$telegram_m .= PHP_EOL;
	$telegram_m .= get_telegram_text_without_link( $text );
	$users      = $users ? explode( ',', $users ) : array();
	if ( $worksection_user_to_email ) {
		$__user_id = email_exists( $worksection_user_to_email );
		if ( $__user_id ) {
			$users[] = $__user_id;
		}
	}
	if ( $administrators ) {
		foreach ( $administrators as $administrator ) {
			$users[] = $administrator->ID;
		}
	}
	if ( $users_ids ) {
		foreach ( $users_ids as $user_ID => $user_name ) {
			$users[] = $user_ID;
		}
	}
	if ( $users ) {
		$users = array_unique( $users );
		foreach ( $users as $user_id ) {
			$user_test = true;
			if ( $comment_id ) {
				$comment_author = get_post_author_id( $comment_id );
				if ( $comment_author == $user_id ) {
					$user_test = false;
				}
			}
			if ( $post_type == 'costs' ) {
				$user_test = carbon_get_user_meta( $user_id, 'super_admin' );
			}
			if ( $user_test ) {
				$post_data       = array(
					'post_type'   => 'notification',
					'post_title'  => $post_title,
					'post_status' => 'publish',
					'post_author' => $user_id
				);
				$notification_id = wp_insert_post( $post_data, true );
				if ( ! is_wp_error( $notification_id ) ) {
					carbon_set_post_meta( $notification_id, 'notification_project_id', $project_id );
					carbon_set_post_meta( $notification_id, 'notification_comment_id', $comment_id );
				}
				$user = get_user_by( 'id', $user_id );
				if ( $comment_id !== 0 ) {
					if ( carbon_get_user_meta( $user_id, 'comment_notification' ) ) {
						if ( carbon_get_user_meta( $user_id, 'email_notification' ) ) {
							send_message( $m, $user->user_email, $post_title );
						}
						$telegram_id = carbon_get_user_meta( $user_id, 'telegram_id' );
						if ( $telegram_id && carbon_get_user_meta( $user_id, 'telegram_notification' ) ) {
							$telegram_users[] = $telegram_id;
						}
					}
				}
			}
		}
	}
	if ( $telegram_users ) {
		foreach ( $telegram_users as $telegram_id ) {
			if ( is_working_hours() ) {
				send_telegram_message( $telegram_id, $telegram_m );
			} else {
				wp_schedule_single_event( get_next_work_timestamp(), 'send_telegram_message_action_hook', array(
					$telegram_id,
					$telegram_m,
					array(),
					false,
					'html'
				) );
			}
		}
	}
}

function create_cron_notification( $args = array() ) {
	$project_id = $args['project_id'] ?? 0;
	$comment_id = $args['comment_id'] ?? 0;
	$text       = $args['text'] ?? '';
	$users_ids  = $args['users_ids'] ?? array();
	wp_schedule_single_event( time() + 10, 'create_notification_action_hook', array(
		(int) $project_id,
		(int) $comment_id,
		$text,
		$users_ids
	) );
}

function create_cron_birthday( $user_id ) {
	if ( $user_id ) {
		$time     = time();
		$args     = array( $user_id );
		$birthday = carbon_get_user_meta( $user_id, 'birthday' );
		if ( wp_next_scheduled( 'create_birthday_action_hook', $args ) ) {
			wp_clear_scheduled_hook( 'create_birthday_action_hook', $args );
		}
		if ( $birthday ) {
			$current_year = date( "Y", $time );
			$arr          = explode( '-', $birthday );
			$next_date    = $arr[0] . '-' . $arr[1] . '-' . $current_year . '09:00';
			$date         = DateTime::createFromFormat( 'd-m-Y H:i', $next_date );
			if ( $date ) {
				$unixTimestamp = $date->getTimestamp();
				if ( $unixTimestamp > $time ) {
					wp_schedule_single_event( $unixTimestamp, 'create_birthday_action_hook', $args );
				} else {
					$date->add( new DateInterval( 'P1Y' ) );
					$unixTimestamp = $date->getTimestamp();
					wp_schedule_single_event( $unixTimestamp, 'create_birthday_action_hook', $args );
				}
			}
		}
	}
}

add_action( 'create_birthday_action_hook', 'birthday_notification', 10, 1 );

function birthday_notification( $user_id ) {
	if ( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( $user ) {
			$time         = time();
			$birthday     = carbon_get_user_meta( $user_id, 'birthday' );
			$fired        = carbon_get_user_meta( $user_id, 'fired' );
			$current_date = date( "d-m-Y", $time );
			$arr          = explode( '-', $birthday );
			$arr1         = explode( '-', $current_date );
			$test         = ( $arr[0] == $arr1[0] ) && ( $arr[1] == $arr1[1] );
			if ( ! $fired ) {
				if ( $test ) {
					$name  = $user->display_name;
					$users = get_users();
					if ( $users ) {
						foreach ( $users as $_user ) {
							$userID = $_user->ID;
							if ( $userID != $user_id ) {
								$_fired                 = carbon_get_user_meta( $userID, 'fired' );
								$_birthday_notification = carbon_get_user_meta( $userID, 'birthday_notification' );
								if ( ! $_fired && $_birthday_notification ) {
									$m       = $name . ' відзначає сьогодні день народження 🎂! Не забудьте привітати!';
									$subject = 'День народження у ' . $name;
									if ( carbon_get_user_meta( $userID, 'email_notification' ) ) {
										send_message( $m, $_user->user_email, $subject );
									}
									$_telegram_notification = carbon_get_user_meta( $userID, 'telegram_notification' );
									$_telegram_id           = carbon_get_user_meta( $userID, 'telegram_id' );
									if ( $_telegram_notification && $_telegram_id ) {
										if ( is_working_hours() ) {
											send_telegram_message( $_telegram_id, $m );
										} else {
											wp_schedule_single_event( get_next_work_timestamp(), 'send_telegram_message_action_hook', array(
												$_telegram_id,
												$m,
												array(),
												false,
												'html'
											) );
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}