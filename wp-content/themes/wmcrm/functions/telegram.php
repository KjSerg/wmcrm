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

function send_telegram_message( $chat_id, $message, $bot_token = false, $parse_mode = 'html' ) {

	$bot_token = $bot_token ?: carbon_get_theme_option( 'telegram_token' );
	$message   = str_replace(
		array(
			"<br>",
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
		$message );
	$message   = strip_tags(
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
	$message = replaceUrl($message);
	$data      = [
		'chat_id'    => (int) $chat_id,
		'text'       => $message,
		"parse_mode" => $parse_mode
	];
	$ch = curl_init( "https://api.telegram.org/bot" . $bot_token . "/sendMessage?" . http_build_query( $data ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HEADER, false );
	$resultQuery = curl_exec( $ch );
	curl_close( $ch );

	return $resultQuery;
}

add_action( 'send_telegram_message_action_hook', 'send_telegram_message', 10, 4 );