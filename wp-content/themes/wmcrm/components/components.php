<?php

function the_user_status( $user_id ) {
	$time    = time();
	$user_id = $user_id ?: get_current_user_id();
	if ( $user_id ) {
		$last_time_online = carbon_get_user_meta( $user_id, 'last_time_online' ) ?: 0;
		$last_time_online = (int) $last_time_online;
		if ( $last_time_online ) {
			$time_diff = $time - $last_time_online;
			$diff      = $time_diff <= 60 ? 'Онлайн' : human_time_diff( $last_time_online );
			$diff_cls  = $time_diff <= 60 ? 'online-status' : '';
			if ( $diff ) {
				echo '<div class="user-status-label ' . $diff_cls . '">' . $diff . '</div>';
			}
		}
	}
}

function the_user_contacts( $user_id ) {
	$user_id = $user_id ?: get_current_user_id();
	if ( $user_id ) {
		$user       = get_user_by( 'id', $user_id );
		$user_tel   = carbon_get_user_meta( $user_id, 'user_tel' );
		$user_email = $user->user_email;
		?>
        <div class="profile-head-contacts">
            <div class="profile-head-contacts__item">
                <div class="icon"><?php _s( _i( 'tel' ) ) ?></div>
                <div class="profile-tel">
					<?php echo $user_tel ?: 'Телефон відсутній'; ?>
                </div>
            </div>
            <div class="profile-head-contacts__item">
                <div class="icon"><?php _s( _i( 'email' ) ) ?></div>
                <div class="profile-email">
					<?php echo $user_email ?: 'Пошта відсутня'; ?>
                </div>
            </div>
        </div>
		<?php
	}
}

function the_user_select_list() {
	$users = get_users();
	if ( $users ):
		?>
        <select class="selectric select-user-quill-js" name="user">
            <option disabled selected>Оберіть</option>
			<?php foreach ( $users as $user ): $userID = $user->ID;
				if ( ! carbon_get_user_meta( $userID, 'fired' ) ): ?>
                    <option value="<?php echo $user->ID; ?>"><?php echo esc_html( $user->display_name ); ?></option>
				<?php endif; endforeach; ?>
        </select>
	<?php
	endif;
}

function the_timer_list() {
	if ( $timers = get_current_timers() ): ?>
        <ul class="timer-list">
			<?php foreach ( $timers as $obj ):
				$user_ID = $obj['user'];
				$status = $obj['status'];
				$current_project = $obj['current_project'];
				$u = get_user_by( 'id', $user_ID );
				$sum_hour = $obj['sum_hour'];;
				?>
                <li class="timer-list-item" data-user-id="<?php echo $user_ID ?>">
                    <a href="<?php echo get_author_posts_url( $user_ID ) ?>"
                       target="_blank"
                       title="<?php echo $u->display_name; ?>"
                       class="timer-list-item__avatar">
                        <img

                                title="<?php echo $u->display_name; ?>"
                                class="cover" src="<?php echo $obj['avatar'] ?>" alt="">
                    </a>
                    <div class="timer-list-item__value">
						<?php
						if ( $sum_hour_arr = explode( ':', $sum_hour ) ) {
							echo $sum_hour_arr[0] . ':' . $sum_hour_arr[1];
						} else {
							echo $obj['sum_hour'];
						}
						?>
                    </div>
					<?php if ( $current_project && get_post( $current_project ) ): ?>
                        <a class="timer-list-item__project link-js open-in-modal"
                           href="<?php echo get_the_permalink( $current_project ) ?>">
							<?php echo get_the_title( $current_project ) ?>
                        </a>
					<?php else: ?>
                        <div class="timer-list-item__project not-active">
							<?php echo $u->display_name; ?> проєкт не вибрав(ла)!
                        </div>
					<?php endif; ?>
                </li>
			<?php
			endforeach; ?>
        </ul>
	<?php endif;
}

function the_timer_html() {
	$is_user_admin = is_current_user_admin();
	if ( $is_user_admin ):

		?>
        <div class="timer">
            <div class="timer-result admin-timers">
                Таймери
            </div>
            <div class="timer-control">
				<?php the_timer_list() ?>
            </div>
        </div>
	<?php
	else:
		?>
        <div class="timer">
            <div class="timer-result">
                Розпочати
            </div>
            <div class="timer-control">
                <div class="timer-control-buttons">
                    <a href="#" class="timer-button timer-button-start">
                        Розпочати робочий день
                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20"
                                                 viewBox="0 0 21 20" fill="none">
  <g clip-path="url(#clip0_314_7791)">
    <path d="M10.5 0C4.96341 0 0.5 4.46341 0.5 10C0.5 15.5366 4.96341 20 10.5 20C16.0366 20 20.5 15.5366 20.5 10C20.5 4.46341 16.0366 0 10.5 0ZM14.939 10.4634L8.28049 14.3659C8.20732 14.4146 8.08537 14.439 8.0122 14.439C7.93902 14.439 7.81707 14.4146 7.7439 14.3659C7.57317 14.2439 7.47561 14.0976 7.47561 13.9024V6.12195H7.45122C7.45122 5.92683 7.57317 5.73171 7.71951 5.65854C7.86585 5.58537 8.10976 5.53659 8.28049 5.65854L14.939 9.53658C15.1098 9.65854 15.2073 9.80488 15.2073 10C15.2073 10.1951 15.0854 10.3902 14.939 10.4634Z"
          fill="white"/>
  </g>
  <defs>
    <clipPath id="clip0_314_7791">
      <rect width="20" height="20" fill="white" transform="matrix(-1 0 0 1 20.5 0)"/>
    </clipPath>
  </defs>
</svg>
                                        </span>
                    </a>
                    <a href="#" class="timer-button timer-button-finish">
                        Закінчити робочий день
                        <span class="icon">
                                          <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20"
                                               viewBox="0 0 21 20" fill="none">
  <g clip-path="url(#clip0_314_7772)">
    <path d="M10.5 0C5 0 0.5 4.5 0.5 10C0.5 15.5 5 20 10.5 20C16 20 20.5 15.5 20.5 10C20.5 4.5 16 0 10.5 0ZM14.0714 12.1429C14.0714 12.9286 13.4286 13.5714 12.6429 13.5714H8.35714C7.57143 13.5714 6.92857 12.9286 6.92857 12.1429V7.85714C6.92857 7.07143 7.57143 6.42857 8.35714 6.42857H12.6429C13.4286 6.42857 14.0714 7.07143 14.0714 7.85714V12.1429Z"
          fill="white"/>
  </g>
  <defs>
    <clipPath id="clip0_314_7772">
      <rect width="20" height="20" fill="white" transform="matrix(-1 0 0 1 20.5 0)"/>
    </clipPath>
  </defs>
</svg>
                                        </span>
                    </a>
                    <a href="#" class="timer-button timer-button-pause">
                        Перерва <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20"
                                                        height="20" viewBox="0 0 20 20" fill="none">
  <g clip-path="url(#clip0_314_7776)">
    <path d="M10 0C4.475 0 0 4.475 0 10C0 15.525 4.475 20 10 20C15.525 20 20 15.525 20 10C20 4.475 15.525 0 10 0ZM8.9 12.5C8.9 13.125 8.4 13.6 7.8 13.6C7.175 13.6 6.7 13.1 6.7 12.5V7.5C6.675 6.9 7.175 6.4 7.775 6.4C8.4 6.4 8.9 6.9 8.9 7.5V12.5ZM13.325 12.5C13.325 13.125 12.825 13.6 12.225 13.6C11.6 13.6 11.125 13.1 11.125 12.5V7.5C11.1 6.9 11.6 6.4 12.2 6.4C12.825 6.4 13.325 6.9 13.325 7.5V12.5Z"
          fill="white"/>
  </g>
  <defs>
    <clipPath id="clip0_314_7776">
      <rect width="20" height="20" fill="white" transform="matrix(-1 0 0 1 20 0)"/>
    </clipPath>
  </defs>
</svg></span>
                    </a>
                    <a href="#" class="timer-button timer-button-play-pause">
                        Продовжити <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="21"
                                                           height="20" viewBox="0 0 21 20" fill="none">
  <g clip-path="url(#clip0_314_7757)">
    <path d="M10.5 0C4.96341 0 0.5 4.46341 0.5 10C0.5 15.5366 4.96341 20 10.5 20C16.0366 20 20.5 15.5366 20.5 10C20.5 4.46341 16.0366 0 10.5 0ZM14.939 10.4634L8.28049 14.3659C8.20732 14.4146 8.08537 14.439 8.0122 14.439C7.93902 14.439 7.81707 14.4146 7.7439 14.3659C7.57317 14.2439 7.47561 14.0976 7.47561 13.9024V6.12195H7.45122C7.45122 5.92683 7.57317 5.73171 7.71951 5.65854C7.86585 5.58537 8.10976 5.53659 8.28049 5.65854L14.939 9.53658C15.1098 9.65854 15.2073 9.80488 15.2073 10C15.2073 10.1951 15.0854 10.3902 14.939 10.4634Z"
          fill="white"/>
  </g>
  <defs>
    <clipPath id="clip0_314_7757">
      <rect width="20" height="20" fill="white" transform="matrix(-1 0 0 1 20.5 0)"/>
    </clipPath>
  </defs>
</svg></span>
                    </a>
                </div>
                <div class="timer-control-results">
                    <div class="timer-work-time">
                        Тривалість робочого дня: <span>00:00:00</span>
                    </div>
                    <div class="timer-pause-time">
                        Тривалість перерви: <span>00:00:00</span>
                    </div>
                </div>
            </div>
        </div>
	<?php
	endif;
}

function the_timer_modal( $args = array() ) {
	date_default_timezone_set( 'Europe/Kiev' );
	$time    = time();
	$user_id = $args['user_id'] ?? wp_get_current_user();
	$date    = $args['date'] ?? date( 'd-m-Y', $time );
	$cost_id = get_cost_id( array(
		'user_id' => $user_id,
		'date'    => $date,
	) );
	if ( $cost_id ) {
		$costs_finish          = carbon_get_post_meta( $cost_id, 'costs_finish' );
		$costs_status          = carbon_get_post_meta( $cost_id, 'costs_status' );
		$costs_start           = carbon_get_post_meta( $cost_id, 'costs_start' );
		$costs_sum_hour        = carbon_get_post_meta( $cost_id, 'costs_sum_hour' );
		$costs_sum_hour_pause  = carbon_get_post_meta( $cost_id, 'costs_sum_hour_pause' );
		$costs_sum_hour_change = carbon_get_post_meta( $cost_id, 'costs_sum_hour_change' );
		$costs_confirmed       = carbon_get_post_meta( $cost_id, 'costs_confirmed' );
		if ( $costs_finish && $costs_status == '0' ) {
			$costs_start  = (int) $costs_start;
			$costs_finish = (int) $costs_finish;
			$costs_start  = $costs_start / 1000;
			$costs_finish = $costs_finish / 1000;
			$user         = get_user_by( 'id', $user_id );
			$avatar       = carbon_get_user_meta( $user_id, 'avatar' );
			if ( ! $avatar ) {
				$avatar = get_avatar_url( $user_id );
			} else {
				$avatar = _u( $avatar, 1 );
			}
			$last_name    = $user->last_name;
			$first_name   = $user->first_name;
			$display_name = $last_name;
			if ( $first_name ) {
				$display_name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
			}
			?>
            <div class="report window-main" id="report-window">
                <div class="report-head">
                    <div class="report-head__title">
                        Звіт за день
                    </div>
                    <div class="report-head-user">
                        <div class="report-head-user__avatar">
                            <img src="<?php echo $avatar; ?>" alt="">
                        </div>
                        <div class="report-head-user__name">
							<?php echo $display_name; ?>
                        </div>
                    </div>
                </div>
                <div class="report-body">
                    <div class="report-body__title">
                        Робочий час
                    </div>
                    <div class="report-body-content">
                        <div class="report-cart">
                            <div class="report-cart__head">
                                Початок
                            </div>
                            <div class="report-cart__body" data-time="<?php echo $costs_start; ?>">
								<?php echo date( 'H:i', $costs_start ); ?>
                            </div>
                        </div>
                        <div class="report-cart">
                            <div class="report-cart__head">
                                Закінчення
                            </div>
                            <div class="report-cart__body">
								<?php echo date( 'H:i', $costs_finish ); ?>
                            </div>
                        </div>
                        <div class="report-cart report-cart--green">
                            <div class="report-cart__head">
                                Робочий час
                            </div>
                            <div class="report-cart__body">
                                <div class="report-cart-sum"> <?php echo $costs_sum_hour_change ?: $costs_sum_hour; ?></div>
                                <div class="report-cart-changed">
									<?php echo $costs_confirmed ? 'Підтверджено ' . $costs_confirmed : ( $costs_sum_hour_change ? 'Змінено' : '' ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="report-cart report-cart--green">
                            <div class="report-cart__head">
                                Перерва
                            </div>
                            <div class="report-cart__body">
								<?php echo $costs_sum_hour_pause; ?>
                            </div>
                        </div>
                    </div>
                </div>
				<?php if ( ! $costs_sum_hour_change ): ?>
                    <div class="report-footer">
                        <div class="report-footer-control">
                            <div class="report-footer__title">Змінити робочий час</div>
                            <a href="#" class="button report-button report-button-trigger">Змінити робочий час</a>
                        </div>
                        <form method="post" id="report-form" class="report-footer-form form-js" novalidate>
                            <input type="hidden" name="id" value="<?php echo esc_attr( $cost_id ); ?>">
                            <input type="hidden" name="action" value="change_user_time">
                            <div class="report-columns">
                                <div class="report-footer__title">Змінити робочий час</div>
                                <label for="" class="form-group report-footer-form__label">
                                    <input type="text" required class="time-input"
                                           name="time" value="<?php echo esc_attr( $costs_sum_hour ); ?>">
                                </label>
                            </div>
                            <label class="form-group">
                                <textarea name="text" placeholder="Коментар до зміни часу..." required></textarea>
                            </label>
                            <button class="button" type="submit">
                                Зберегти зміни
                            </button>
                        </form>
                    </div>
				<?php endif; ?>
            </div>
			<?php
		}
	}
}

function the_autocomplete_input( $args = array() ) {
	$input_name  = $args['input_name'];
	$action      = $args['action'] ?? 'get_projects_list';
	$title       = $args['title'] ?? '';
	$placeholder = $args['placeholder'] ?? '';
	$exclude     = $args['exclude'] ?? '';
	?>
    <label class="form-group autocomplete">
        <input type="hidden" name="<?php echo $input_name ?>" class="autocomplete-value">
		<?php if ( $title ): ?><span class="form-group__title"><?php echo $title ?></span><?php endif; ?>
        <input type="text"
               data-action="<?php echo $action; ?>"
               data-exclude="<?php echo $exclude; ?>"
               value=""
               class="autocomplete-input autocomplete-text"
               placeholder="<?php echo $placeholder ?>">
        <span class="autocomplete-wrapper" style="display: none"></span>
    </label>
	<?php
}