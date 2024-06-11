<?php

function absences_action() {
	$action = $_GET['action'] ?? '';
	$id     = $_GET['id'] ?? '';
	if ( is_current_user_admin() ) {
		if ( $action == 'confirm_absences' || $action == 'remove_absences' && $id && get_post( $id ) ) {
			$id = (int) $id;
			if ( $action == 'confirm_absences' && get_post_status($id) != 'publish' ) {
				$my_post = array(
					'ID'          => $id,
					'post_status' => 'publish',
				);
				$id      = wp_update_post( $my_post, true );
				if ( is_wp_error( $id ) ) {
					echo '<div class="admin-warning">' . $id->get_error_message() . '</div>';
				} else {
					echo '<div class="admin-notification">Відгул підтверджений</div>';
				}
			}
			if ( $action == 'remove_absences' ) {
				if(wp_delete_post( $id )){
					echo '<div class="admin-notification">Відгул видалено і непідтверджений</div>';
				}
			}
		}
	}
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
				if ( ! $costs_confirmed ) {
					carbon_set_post_meta( $id, 'costs_confirmed', date( 'd.m.Y', $time ) );
				}
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