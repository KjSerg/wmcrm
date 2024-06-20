<?php

function check_telegram_authorization( $auth_data ) {
	$bot_username = carbon_get_theme_option( 'telegram_bot_name' );
	$bot_token    = carbon_get_theme_option( 'telegram_token' );
	$check_hash   = $auth_data['hash'];
	unset( $auth_data['hash'] );
	$data_check_arr = [];
	foreach ( $auth_data as $key => $value ) {
		$data_check_arr[] = $key . '=' . $value;
	}
	sort( $data_check_arr );
	$data_check_string = implode( "\n", $data_check_arr );
	$secret_key        = hash( 'sha256', $bot_token, true );
	$hash              = hash_hmac( 'sha256', $data_check_string, $secret_key );
	if ( strcmp( $hash, $check_hash ) !== 0 ) {
		throw new Exception( 'Data is NOT from Telegram' );
	}
	if ( ( time() - $auth_data['auth_date'] ) > 86400 ) {
		throw new Exception( 'Data is outdated' );
	}

	return $auth_data;
}

function get_user_by_telegram( $telegram_id ) {
	$res    = array();
	$params = array(
		'meta_query' => array(
			array(
				'key'     => 'telegram_id',
				'value'   => $telegram_id,
				'compare' => '='
			)
		)
	);
	$uq     = new WP_User_Query( $params );
	if ( ! empty( $uq->results ) ) {
		foreach ( $uq->results as $u ) {
			$res = $u;
		}
	}

	return empty( $res ) ? false : $res;
}

function on_telegram_auth() {
	if ( isset( $_GET['hash'] ) ) {
		try {
			$var       = variables();
			$set       = $var['setting_home'];
			$assets    = $var['assets'];
			$url       = $var['url'];
			$url_home  = $var['url_home'];
			$auth_data = check_telegram_authorization( $_GET );
			if ( $auth_data ) {
				$telegram_id = $auth_data['id'];
				$username    = $auth_data['username'];
				$first_name  = $auth_data['first_name'];
				$last_name   = $auth_data['last_name'];
				$photo_url   = $auth_data['photo_url'];
				$user        = get_user_by_telegram( $telegram_id );
				$_user_id    = get_current_user_id();
				if ( $user ) {
					$_user_id = $user->ID;
				}
				carbon_set_user_meta( $_user_id, 'telegram_id', $telegram_id );
				carbon_set_user_meta( $_user_id, 'telegram_image', $photo_url );
			}

		} catch ( Exception $e ) {
			die ( $e->getMessage() );
		}
	}
}

function send_telegram_message( $chat_id, $message, $keyboard = array(), $bot_token = false, $parse_mode = 'html' ) {

	$bot_token = $bot_token ?: carbon_get_theme_option( 'telegram_token' );
//	$sessions  = carbon_get_theme_option( 'sessions' ) ?: array();
	$data      = [
		'chat_id'    => (int) $chat_id,
		'text'       => $message,
		"parse_mode" => $parse_mode
	];
	if ( $keyboard ) {
		$data['reply_markup'] = json_encode( $keyboard );
	}
	$ch = curl_init( "https://api.telegram.org/bot" . $bot_token . "/sendMessage?" . http_build_query( $data ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HEADER, false );
	$resultQuery = curl_exec( $ch );
	curl_close( $ch );
//	$sessions[] = array(
//		'connect_id'   => $message,
//		'last_message' => json_encode( $resultQuery ),
//	);
//	carbon_set_theme_option( 'sessions', $sessions );

	return $resultQuery;
}

add_action( 'send_telegram_message_action_hook', 'send_telegram_message', 10, 5 );

function get_telegram_text( $text ) {
	$message = str_replace(
		array(
			"<p></p>",
			"<hr>",
			"h1",
			"h2",
			"h3",
		),
		array(
			PHP_EOL,
			PHP_EOL . PHP_EOL,
			'strong',
			'strong',
			'strong'
		),
		$text );
	$message = strip_tags(
		$message,
		array(
			'a',
			'b',
			'strong',
			'i',
			'em',
			'ins',
			's',
			'del',
			'pre',
		)
	);

	return $message;
}

function get_telegram_text_without_link( $text ) {
	$message = str_replace(
		array(
			"<p></p>",
			"<hr>",
			"h1",
			"h2",
			"h3",
		),
		array(
			PHP_EOL,
			PHP_EOL . PHP_EOL,
			'strong',
			'strong',
			'strong'
		),
		$text );
	$message = strip_tags(
		$message,
		array(
			'b',
			'strong',
			'i',
			'em',
			'ins',
			's',
			'del',
			'pre',
		)
	);

	return $message;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'crm/v1', '/telegram_callback', array(
		'methods'             => 'POST',
		'callback'            => 'handle_telegram_callback',
		'permission_callback' => '__return_true', // Для тестування, потім краще зробити перевірку
	) );
} );

function handle_telegram_callback( WP_REST_Request $request ) {
	$body   = $request->get_body();
	$update = json_decode( $body, true );
	$token  = carbon_get_theme_option( 'telegram_token' );
	if ( isset( $update['callback_query'] ) ) {
		$callback_query = $update['callback_query'];
		$callback_data  = $callback_query['data'];
		$chat_id        = $callback_query['message']['chat']['id'];
		$parts          = explode( ':', $callback_data );
		$button         = $parts[0];
		$param          = $parts[1];
		$value          = $parts[2];
		$response       = "";
		if ( $param == 'id' && $value ) {
			$id = (int) $value;
			if ( get_post( $id ) && get_post_status( $id ) != 'publish' ) {
				$my_post = array(
					'ID'          => $id,
					'post_status' => 'publish',
				);
				$id      = wp_update_post( $my_post, true );
				if ( ! is_wp_error( $id ) ) {
					$user_id = get_post_author_id( $id );
					$user    = get_user_by( 'id', $user_id );
					if ( $user_id ) {
						if ( ! carbon_get_user_meta( $user_id, 'fired' ) ) {
							$start_date  = carbon_get_post_meta( $id, 'absences_start_date' );
							$finish_date = carbon_get_post_meta( $id, 'absences_finish_date' );
							$reasons     = get_the_terms( $id, 'reasons' );
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
								$response .= PHP_EOL . $text . " для " . $user->display_name;
							}else{
								$response .= "Користувача не існує";
							}
						}else{
							$response .= "Користувача вже звільнено відсутність неактуальна";
						}
					}else{
						$response .= "Користувача не існує";
					}
				}else{
					$response .= $id->get_error_message();
				}
			}else{
				$response .= "Відпустку ID:$value не знайдено або вже погодженно!";
			}
		}else{
			$response .= "Передані не вірні параметри $param:$value";
		}
		$url         = "https://api.telegram.org/bot$token/sendMessage";
		$post_fields = [
			'chat_id' => $chat_id,
			'text'    => $response
		];
		$ch          = curl_init();
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			"Content-Type:multipart/form-data"
		) );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
		$output = curl_exec( $ch );
		curl_close( $ch );

		return new WP_REST_Response( 'Success', 200 );
	}

	return new WP_REST_Response( 'No callback query found', 400 );
}