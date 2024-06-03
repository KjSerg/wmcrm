<?php
function the_events_section( $container = true ) {
	$birthdays = get_birthdays();
	$query     = new WP_Query( array(
		'post_type'      => 'events',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
	) );
	if ( $container ):
		?>
        <section class="section events-section">
        <div class="container">
	<?php
	endif;
	if ( $birthdays ) {
		foreach ( $birthdays as $userID => $user ) {
			the_birthday( $user );
		}
	}
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) {
			$query->the_post();
			the_event();
		}
	endif;
	if ( $container ):
		?>
        </div>
        </section>
	<?php
	endif;
	wp_reset_postdata();
	wp_reset_query();
}

function the_birthday( $user ) {
	$userID          = $user->ID;
	$current_user_id = get_current_user_id();
	$avatar          = carbon_get_user_meta( $userID, 'avatar' );
	$avatar          = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $userID );
	$last_name      = $user->last_name;
	$first_name     = $user->first_name;
	$performer_name = $last_name;
	if ( $first_name ) {
		$performer_name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
	}
    $string = esc_html( $performer_name );
    if($userID == $current_user_id) {
	    $string .= ' вітаємо із днем народження!';
    }else{
	    $string .= ' сьогодні святкує день народження!';
    }
	?>
    <div class="events-item events-item-birthday">
        <div class="events-item-user__avatar">
            <img src="<?php echo $avatar; ?>" alt="<?php echo $performer_name; ?>">
        </div>
        <div class="events-item-user__name">
		    <?php echo esc_html( $string ) ?>
        </div>
    </div>
	<?php
}

function the_event( $id = false, $show = false ) {
	$user_id            = get_current_user_id();
	$id                 = $id ?: get_the_ID();
	$get_user_result    = get_user_event_result_id( $id, $user_id );
	$event_acquainted   = carbon_get_post_meta( $get_user_result, 'event_acquainted' );
	$show_test          = ! $event_acquainted;
	if ( $show ) {
		$show_test = true;
	}
	if ( $show_test ):
		$get_results = get_event_result( $id );
		$counted_values = array_count_values( $get_results );
		$users_count    = count_all_users();
		$event_question = carbon_get_post_meta( $id, 'event_question' );
		$event_answers  = carbon_get_post_meta( $id, 'event_answers' );
		$event_multiple = carbon_get_post_meta( $id, 'event_multiple' );
		$input_type     = $event_multiple ? 'checkbox' : 'radio';
		$author_id      = get_post_field( 'post_author', $id );
		$avatar         = carbon_get_user_meta( $author_id, 'avatar' );
		$avatar         = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $author_id );
		$user           = get_user_by( 'id', $author_id );
		$last_name      = $user->last_name;
		$first_name     = $user->first_name;
		$performer_name = $last_name;
		if ( $first_name ) {
			$performer_name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
		}
		?>
        <form class="events-item form-js" method="post" novalidate id="event-<?php echo $id; ?>">
            <div class="events-item-column">
                <input type="hidden" name="action" value="save_event_result">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="show_test" value="<?php echo $show; ?>">
                <input type="hidden" name="is_vote" class="vote-status-input" value="0">
                <div class="events-item-user">
                    <div class="events-item-user__avatar">
                        <img src="<?php echo $avatar; ?>" alt="<?php echo $performer_name; ?>">
                    </div>
                    <div class="events-item-user__name">
						<?php echo esc_html( $performer_name ) ?>
                    </div>
                </div>
                <div class="events-item__title">
					<?php echo get_the_title( $id ) ?>
                </div>
                <div class="text events-item__text">
					<?php echo replace_url( get_content_by_id( $id ) ); ?>
                </div>
				<?php if ( $event_question && $event_answers ): ?>
                    <div class="events-item__title">
						<?php echo $event_question; ?>
                    </div>
                    <div class="events-item-answers">
						<?php foreach ( $event_answers as $answer_index => $answer ):
							if ( $get_user_result === 0 ):
								?>
                                <div class="input-container">
                                    <label class="input-container-label">
                                        <input type="<?php echo $input_type; ?>" name="answers[]"
                                               value="<?php echo $answer_index; ?>">
                                        <span class="input-container-icon"><svg width="9" height="7" viewBox="0 0 9 7"
                                                                                fill="none"
                                                                                xmlns="http://www.w3.org/2000/svg">
<path d="M8.4936 0.421227C8.22455 0.151824 7.78767 0.151994 7.51826 0.421227L3.12858 4.81108L1.17756 2.86007C0.908153 2.59067 0.471456 2.59067 0.202052 2.86007C-0.0673508 3.12947 -0.0673508 3.56617 0.202052 3.83558L2.64073 6.27425C2.77534 6.40887 2.95186 6.47635 3.1284 6.47635C3.30493 6.47635 3.48162 6.40904 3.61623 6.27425L8.4936 1.39671C8.763 1.1275 8.763 0.690613 8.4936 0.421227Z"
      fill="white"/>
</svg></span>
                                    </label>
                                    <div class="input-container__text">
										<?php echo replace_url( $answer['answer'] ) ?>
                                    </div>
                                </div>
							<?php else:
								$event_result_answers = carbon_get_post_meta( $get_user_result, 'event_result_answers' );
								$event_result_answers = explode( ',', $event_result_answers );
								$attr = in_array( $answer_index, $event_result_answers ) ? 'checked' : '';
								$percent = $counted_values[ $answer_index ] ? get_percent( $users_count, $counted_values[ $answer_index ] ) : 0;
								?>
                                <div class="events-item-result">
                                    <div class="input-container">
                                        <label class="input-container-label">
                                            <input type="<?php echo $input_type; ?>" disabled
												<?php echo $attr; ?>
                                                   name="answers[]"
                                                   value="<?php echo $answer_index; ?>">
                                            <span class="input-container-icon"><svg width="9" height="7"
                                                                                    viewBox="0 0 9 7"
                                                                                    fill="none"
                                                                                    xmlns="http://www.w3.org/2000/svg">
<path d="M8.4936 0.421227C8.22455 0.151824 7.78767 0.151994 7.51826 0.421227L3.12858 4.81108L1.17756 2.86007C0.908153 2.59067 0.471456 2.59067 0.202052 2.86007C-0.0673508 3.12947 -0.0673508 3.56617 0.202052 3.83558L2.64073 6.27425C2.77534 6.40887 2.95186 6.47635 3.1284 6.47635C3.30493 6.47635 3.48162 6.40904 3.61623 6.27425L8.4936 1.39671C8.763 1.1275 8.763 0.690613 8.4936 0.421227Z"
      fill="white"/>
</svg></span>
                                        </label>
                                        <div class="input-container__text">
											<?php echo replace_url( $answer['answer'] ) ?>
                                        </div>
                                    </div>
                                    <div class="events-item-result-wrapper">
                                        <div class="events-item-result-shame">
                                            <span style="width: <?php echo $percent; ?>%"></span>
                                        </div>
                                        <div class="events-item-result-num">
											<?php echo $counted_values[ $answer_index ] ?? 0 ?> /
                                            <span><?php echo $users_count; ?></span>
                                        </div>
                                    </div>
                                </div>
							<?php
							endif;
						endforeach; ?>
                    </div>
					<?php if ( $get_user_result === 0 ): ?>
                        <div class="events-item-button-wrapper">
                            <a class="form-button button vote-js" href="#">
                                Проголусувати
                            </a>
                        </div>
					<?php endif; ?>
				<?php endif; ?>
            </div>
			<?php if ( ! $event_acquainted ): ?>
                <div class="events-item-column">
                    <button class="form-button button" type="submit">
                        Ознайомився
                    </button>
                </div>
			<?php endif; ?>
        </form>
	<?php
	endif;
}