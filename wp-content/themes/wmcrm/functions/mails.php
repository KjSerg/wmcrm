<?php

function send_notification( $user_id, $ID ) {
	if ( carbon_get_user_meta( $user_id, 'project_notification' ) ) {
		$c                 = true;
		$message           = '';
		$project_name      = get_bloginfo( 'name' );
		$admin_email       = get_bloginfo( 'admin_email' );
		$var               = variables();
		$set               = $var['setting_home'];
		$url               = $var['url'];
		$title             = get_the_title( $ID );
		$permalink         = get_the_permalink( $ID );
		$user_form_subject = "Вам назначений новий проєкт";
		$string_1          = 'Назва';
		$string2           = 'Проєкт';
		$string3           = "<a href='$permalink' target='_blank'>$title ID:$ID</a>";
		$user              = get_user_by( 'ID', (int) $user_id );
		$email             = $user->user_email;
		$message           .= "
		" . ( ( $c = ! $c ) ? ' <tr>' : ' <tr style="background-color: #f8f8f8;"> ' ) . "
		<td style='padding: 10px; border: #e9e9e9 1px solid;' ><b> $string2</b></td>
		<td style='padding: 10px; border: #e9e9e9 1px solid;' > $string3</td>
		</tr>
		  ";
		$message           .= "
		" . ( ( $c = ! $c ) ? '<tr>' : '<tr style="background-color: #f8f8f8;">' ) . "
		<td style='padding: 10px; border: #e9e9e9 1px solid;'><b> $string_1</b></td>
		<td style='padding: 10px; border: #e9e9e9 1px solid;'> $title</td>
		</tr>
		  ";
		$message           = "<table style='width: 100%;'> $message</table> ";
		$headers           = "MIME-Version:1.0" . PHP_EOL .
		                     "Content-Type:text/html; charset=utf-8" . PHP_EOL .
		                     'From:' . ___adopt( $project_name ) . ' <new_project@' . $_SERVER['HTTP_HOST'] . '>' . PHP_EOL .
		                     'Reply-To: ' . $admin_email . '' . PHP_EOL;
		if ( carbon_get_user_meta( $user_id, 'email_notification' ) ) {
			wp_mail( $email, $user_form_subject, $message, $headers );
		}
		$telegram_id = carbon_get_user_meta( $user_id, 'telegram_id' );
		if ( $telegram_id && carbon_get_user_meta( $user_id, 'telegram_notification' ) ) {
			$message_telegram = $user_form_subject . ': "' . $string3 . '"';
			if ( is_working_hours() ) {
				send_telegram_message( $telegram_id, $message_telegram );
			} else {
				wp_schedule_single_event( get_next_work_timestamp(), 'send_telegram_message_action_hook', array(
					$telegram_id,
					$message_telegram,
					false,
					'html'
				) );
			}
		}
	}
}

function send_reset_password( $user_id ) {
	$c            = true;
	$message      = '';
	$project_name = get_bloginfo( 'name' );
	$var          = variables();
	$set          = $var['setting_home'];
	$url          = $var['url'];
	$form_subject = 'Новий пароль для авторизації на сайті';
	$string       = 'Ваш новий пароль:';
	$user         = get_user_by( 'ID', (int) $user_id );
	$email        = $user->user_email;
	$password     = wp_generate_password( 16, true, true );
	wp_set_password( $password, (int) $user_id );
	$message .= "
		" . ( ( $c = ! $c ) ? '<tr>' : '<tr style="background-color: #f8f8f8;">' ) . "
		<td style='padding: 10px; border: #e9e9e9 1px solid;' ><b > $string</b ></td >
		<td style='padding: 10px; border: #e9e9e9 1px solid;' > $password</td >
		</tr>
		  ";
	$message = "<table style='width: 100%;'> $message</table> ";
	$headers = "MIME-Version:1.0" . PHP_EOL .
	           "Content-Type:text/html; charset=utf-8" . PHP_EOL .
	           'From:' . ___adopt( $project_name ) . ' <reset_password@' . $_SERVER['HTTP_HOST'] . '>' . PHP_EOL .
	           'Reply-To: ' . $email . '' . PHP_EOL;
	wp_mail( $email, $form_subject, $message, $headers );
}

function send_message( $m, $email = false, $form_subject = 'Повідомлення із CRM' ) {
	$c            = true;
	$message      = $m;
	$project_name = get_bloginfo( 'name' );
	$email        = $email ?: get_bloginfo( 'admin_email' );
	$var          = variables();
	$set          = $var['setting_home'];
	$url          = $var['url'];
	$headers      = "MIME-Version:1.0" . PHP_EOL .
	                "Content-Type:text/html; charset=utf-8" . PHP_EOL .
	                'From:' . ___adopt( $project_name ) . ' <notification@' . $_SERVER['HTTP_HOST'] . '>' . PHP_EOL .
	                'Reply-To: ' . $email . '' . PHP_EOL;
	wp_mail( $email, $form_subject, $message, $headers );
}