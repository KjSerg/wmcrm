<?php

function the_user_status( $user_id ) {
	$time    = time();
	$user_id = $user_id ?: get_current_user_id();
	if ( $user_id ) {
		$last_time_online = carbon_get_user_meta( $user_id, 'last_time_online' ) ?: 0;
		$last_time_online = (int) $last_time_online;
		if ( $last_time_online ) {
			$time_diff = $time - $last_time_online;
			$diff      = $time_diff <= 90 ? 'Онлайн' : human_time_diff( $last_time_online );
			$diff_cls  = $time_diff <= 90 ? 'online-status' : '';
			if ( $diff ) {
				echo '<div class="user-status-label ' . $diff_cls . '">' . $diff . '</div>';
			}
		}
	}
}

function the_user_row( $_user ) {
	$_user_id           = $_user->ID;
	$user_id            = get_current_user_id();
	if ( $_user_id != $user_id ):
		$_avatar = carbon_get_user_meta( $_user_id, 'avatar' );
		$_avatar        = $_avatar ? _u( $_avatar, 1 ) : get_avatar_url( $_user_id );
		$is_fired       = carbon_get_user_meta( $_user_id, 'fired' );
		$is_super_admin = carbon_get_user_meta( $_user_id, 'super_admin' );
		?>
        <div class="users-table-body-row" data-user="<?php echo $_user_id; ?>">
            <div class="users-table-column">
                <div class="users-table-item">
                    <a href="<?php echo $_avatar; ?>"
                       class="users-table-item__avatar modal-open">
                        <img class="cover" src="<?php echo $_avatar; ?>" alt="">
                    </a>
                    <a href="<?php echo get_author_posts_url( $_user_id ) ?>"
                       class="users-table-item__name link-js">
						<?php echo $_user->display_name; ?>
                    </a>
					<?php the_user_status( $_user_id ); ?>
                </div>
            </div>
            <div class="users-table-column">
                <div class="users-table__position">
					<?php echo carbon_get_user_meta( $_user_id, 'position' ) ?: "Посада відсутня" ?>
                </div>
            </div>
            <div class="users-table-column">
				<?php the_user_contacts( $_user_id ); ?>
            </div>
            <div class="users-table-column">
                <div class="users-table-controls">
                    <a href="#change-user-<?php echo $_user_id; ?>"
                       class="users-table-control modal-open">
						<?php _s( _i( 'icon1' ) ) ?>
                    </a>
					<?php if ( ! $is_super_admin ): ?>
                        <a href="#dismiss-user-<?php echo $_user_id; ?>" class="users-table-control modal-open">
							<?php
							if ( $is_fired ) {
								_s( _i( 'check' ) );
							} else {
								_s( _i( 'icon2' ) );
							}
							?>
                        </a>
					<?php endif; ?>
                </div>
            </div>
        </div>
        <div class="create-window modal-window" id="change-user-<?php echo $_user_id; ?>">
            <div class="title">
                Редагувати особисту інформацію
            </div>
            <form class="form form-js change-user-form" id="change-user-form-<?php echo $_user_id; ?>" method="post">
                <input type="hidden" name="action" value="change_user">
                <input type="hidden" name="user_id" value="<?php echo $_user_id; ?>">
                <div class="form-row">
                    <label class="form-group half">
                        <span class="form-group__title"> Прізвище</span>
                        <input type="text" name="last_name" required
                               value="<?php echo $_user->last_name; ?>"
                               placeholder="Введіть  прізвище">
                    </label>
                    <label class="form-group half">
                        <span class="form-group__title"> Імя</span>
                        <input type="text" name="first_name" required
                               value="<?php echo $_user->first_name; ?>"
                               placeholder="Введіть  імя">
                    </label>
                </div>
                <div class="form-row">
                    <label class="form-group half">
                        <span class="form-group__title"> Email</span>
                        <input type="email" name="email" required
                               value="<?php echo $_user->user_email; ?>"
                               data-reg="[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])"
                               placeholder="Введіть  email">
                    </label>
                    <label class="form-group half">
                        <span class="form-group__title"> Телефон</span>
                        <input type="tel" name="tel"
                               value="<?php echo carbon_get_user_meta( $_user_id, 'user_tel' ) ?>"
                               placeholder="Введіть номер телефону">
                    </label>
                </div>
                <div class="form-row">
                    <label class="form-group half">
                        <span class="form-group__title"> Посада</span>
                        <input type="text" name="position"
                               value="<?php echo carbon_get_user_meta( $_user_id, 'position' ) ?>"
                               placeholder="Введіть посаду">
                    </label>
                    <label class="form-group half">
                        <span class="form-group__title"> День народження</span>
                        <input name="birthday"
                               readonly
                               class="date-input"
                               value="<?php echo carbon_get_user_meta( $_user_id, 'birthday' ) ?: '01-01-1995'; ?>"
                               placeholder="День народження">
                    </label>
                </div>
                <label class="form-group ">
                    <span class="form-group__title"> Worksection id</span>
                    <input type="text" name="worksection_id"
                           value="<?php echo carbon_get_user_meta( $_user_id, 'worksection_id' ) ?>"
                           placeholder="Введіть worksection_id">
                </label>
                <div class="form-buttons">
                    <button class="form-button button">
                        Змінити
                    </button>
                </div>
            </form>
        </div>
        <div class="dismiss-user-window modal-window" id="dismiss-user-<?php echo $_user_id; ?>">
			<?php if ( $is_fired ): ?>
                <div class="modal-window__title">Повернути в команду <?php echo $_user->display_name; ?>?</div>
                <div class="modal-window__subtitle">
                    Буде переміщено із розділу звільнених та буде надано доступ до CRM
                </div>
                <div class="form-buttons">
                    <a class="form-button button return-user__button" href="#" data-user-id="<?php echo $_user_id; ?>">
                        Повернути користувача
                    </a>
                </div>
			<?php else: if ( ! $is_super_admin ): ?>
                <div class="modal-window__title">Звільнити <?php echo $_user->display_name; ?>?</div>
                <div class="modal-window__subtitle">Буде переміщено в розділ звільнених та видалиться доступ CRM</div>
                <div class="form-buttons">
                    <a class="form-button button dismiss-user__button" href="#" data-user-id="<?php echo $_user_id; ?>">
                        Звільнити користувача
                    </a>
                </div>
			<?php endif; ?>
			<?php endif; ?>
        </div>
	<?php endif;
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
				<?php if ( $user_tel ): ?>
                    <a href="tel:<?php echo esc_attr( $user_tel ); ?>" class="profile-tel">
						<?php echo esc_html( $user_tel ); ?>
                    </a>
				<?php else: ?>
                    <div class="profile-tel">
                        Телефон відсутній
                    </div>
				<?php endif; ?>
            </div>
            <div class="profile-head-contacts__item">
                <div class="icon"><?php _s( _i( 'email' ) ) ?></div>
				<?php if ( $user_tel ): ?>
                    <a href="mailto:<?php echo esc_attr( $user_email ); ?>" class="profile-email">
						<?php echo esc_html( $user_email ); ?>
                    </a>
				<?php else: ?>
                    <div class="profile-email">
                        Пошта відсутня
                    </div>
				<?php endif; ?>
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
	if ( $timers = get_current_timers() ):
		?>
        <ul class="timer-list">
			<?php foreach ( $timers as $obj ):
				$timer_ID = $obj['ID'];
				$user_ID = $obj['user'];
				$status = $obj['status'];
				$current_project = $obj['current_project'];
				$u = get_user_by( 'id', $user_ID );
				$sum_hour = $obj['sum_hour'];
				$status = $obj['status'];
				$text_list = $obj['text_list'] ?? array();
				$text_list_str = $text_list ? $text_list[0]['text'] : '';
				$cls = '';
				if ( $status == '-1' ) {
					$cls = ' pause';
				} elseif ( $status == '0' ) {
					$cls = ' stop';
				}
				$res  = get_stopwatches( $timer_ID );
				$test = $res && isset( $res['work']['string'] );
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
                    <div class="timer-list-item__value <?php echo $cls; ?>" title='<?php echo $text_list_str; ?>'>
						<?php
						if ( $test ) {
							if ( $sum_hour_arr = explode( ':', $res['work']['string'] ) ) {
								echo $sum_hour_arr[0] . ':' . $sum_hour_arr[1];
							} else {
								echo $res['work']['string'];
							}
						} else {
							if ( $sum_hour_arr = explode( ':', $sum_hour ) ) {
								echo $sum_hour_arr[0] . ':' . $sum_hour_arr[1];
							} else {
								echo $obj['sum_hour'];
							}
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
	$is_user_admin                = is_current_user_admin();
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
		$user_ID = get_current_user_id();
		$today                    = date( 'd-m-Y', time() );
		$cost_id                  = get_cost_id( array(
			'user_id' => $user_ID,
			'date'    => $today,
		) );
		$selected_project_id      = carbon_get_user_meta( $user_ID, 'current_project' );
		if ( $cost_id ):
			$costs_sum_hour = carbon_get_post_meta( $cost_id, 'costs_sum_hour' ) ?: '00:00:00';
			$costs_sum_pause_hour = carbon_get_post_meta( $cost_id, 'costs_sum_hour_pause' ) ?: '00:00:00';
			$costs_status         = carbon_get_post_meta( $cost_id, 'costs_status' );
			$cls                  = '';
			if ( $costs_status == - 1 ) {
				$cls = ' pause';
			} elseif ( $costs_status == 1 ) {
				$cls = ' play';
			}
			?>
            <div class="timer not-active <?php echo $cls ?> ">
                <div class="timer-result">
					<?php echo $costs_sum_hour ?>
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
                            Перерва
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20"
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
                            Продовжити
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="21"
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
                            Тривалість робочого дня:
                            <span><?php echo $costs_sum_hour ?></span>
                        </div>
                        <div class="timer-pause-time">
                            Тривалість перерви:
                            <span><?php echo $costs_sum_pause_hour ?></span>
                        </div>
                        <a href="<?php echo $selected_project_id ? get_the_permalink( $selected_project_id ) : '#' ?>"
                           class="timer-project link-js">
                            Проєкт:
                            <span><?php echo $selected_project_id ? get_the_title( $selected_project_id ) : 'Не вибрано'; ?></span>
                        </a>
                    </div>
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
                            Перерва
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20"
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
                            Продовжити
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="21"
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
                            Тривалість робочого дня:
                            <span>00:00:00</span>
                        </div>
                        <div class="timer-pause-time">
                            Тривалість перерви:
                            <span>00:00:00</span>
                        </div>
                    </div>
                </div>
            </div>
		<?php
		endif;
	endif;
}

function the_timer_modal( $args = array() ) {
	date_default_timezone_set( 'Europe/Kiev' );
	$time            = time();
	$current_user_id = get_current_user_id();
	$user_id         = $args['user_id'] ?? wp_get_current_user();
	$date            = $args['date'] ?? date( 'd-m-Y', $time );
	$cost_id         = get_cost_id( array(
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
		$costs_start           = (int) $costs_start;
		$costs_finish          = (int) $costs_finish;
		$costs_start           = $costs_start / 1000;
		$costs_finish          = $costs_finish / 1000;
		$user                  = get_user_by( 'id', $user_id );
		$avatar                = carbon_get_user_meta( $user_id, 'avatar' );
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
                        <img class="cover" src="<?php echo $avatar; ?>" alt="">
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
							<?php echo $costs_finish ? date( 'H:i', $costs_finish ) : '-'; ?>
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
			<?php if ( $current_user_id == $user_id ): ?>
				<?php if ( ! $costs_sum_hour_change ):
					$t = explode( ':', $costs_sum_hour );
					?>
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
                                           name="time" value="<?php echo esc_attr( $t[0] . ':' . $t[1] ); ?>">
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
			<?php endif; ?>
        </div>
		<?php
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
		<?php if ( $title ): ?>
            <span class="form-group__title"><?php echo $title ?></span><?php endif; ?>
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

function the_absences( $id = false ) {
	$id              = $id ?: get_the_ID();
	$current_user_id = get_current_user_id();
	$is_admin        = is_current_user_admin();
	$author_id       = get_post_field( 'post_author', $id );
	$test            = $author_id == $current_user_id;
	$test            = $test ?: $is_admin;
	if ( $test ):
		$post_status = get_post_status( $id );
		$user        = get_user_by( 'id', $author_id );
		$avatar      = carbon_get_user_meta( $author_id, 'avatar' );
		$avatar      = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $author_id );
		$link        = get_post_type_archive_link( 'absences' ) . '?action=confirm_absences&id=' . $id;
		$remove_link = get_post_type_archive_link( 'absences' ) . '?action=remove_absences&id=' . $id;
		$reasons     = get_the_terms( $id, 'reasons' );
		$start_date  = carbon_get_post_meta( $id, 'absences_start_date' );
		$finish_date = carbon_get_post_meta( $id, 'absences_finish_date' );
		$str         = '';
		if ( $start_date == $finish_date ) {
			$str = 'дата відсутності ' . $start_date;
		} else {
			$str = '(від ' . $start_date . ' до ' . $finish_date . ')';
		}
		?>
        <div class="comment absences-item"
             id="absences-<?php echo $id ?>">
            <div class="comment-head">
                <div class="comment-author">
					<?php if ( $avatar ) {
						echo "<div class='comment-author__avatar'><img class='cover' src='$avatar' alt=''/></div>";
					} ?>
					<?php echo $user->display_name ?>
                </div>
                <div class="comment-date">
					<?php echo get_the_date( 'd-m-Y H:i', $id ); ?>
					<?php if ( $reasons ) {
						echo '[' . $reasons[0]->name . ']';
					} ?>
                </div>
            </div>
            <div class="comment-content text">
				<?php echo replace_url( get_content_by_id( $id ) ); ?>
                <br>
                <strong><?php echo $str; ?></strong>
            </div>
            <div class="absences-item-controls">
				<?php if ( is_current_user_admin() && $post_status == 'pending' ): ?>
                    <a href="<?php echo $link; ?>" class="button">Підтвердити</a>
				<?php endif; ?>
                <a href="<?php echo $remove_link; ?>" class="button button--bordered">Відмінити</a>
            </div>
        </div>
	<?php
	endif;
}

function the_presets_select() {
	$args  = array(
		'post_type'      => 'presets',
		'posts_per_page' => - 1,
		'post_status'    => 'publish'
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		?>
        <select name="presets" class="presets-select selectric" id="presets-select">
        <option value="">Виберіть шаблон проєкта</option>
		<?php
		while ( $query->have_posts() ) {
			$query->the_post();
			$id    = get_the_ID();
			$title = get_the_title();
			?>
            <option value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php
		}
		?></select><?php
	}
	wp_reset_postdata();
	wp_reset_query();
}

function the_absence_item( $args = array() ) {
	$user_id = $args['user_id'] ?? get_current_user_id();
	$date    = $args['date'] ?? false;
	if ( $date && $user_id ) {
		$date  = explode( '-', $date );
		$items = get_absences_list( $date[1], $date[2], $user_id );

		if ( $items ) {
			foreach ( $items as $_id => $item ) {
				if ( is_date_in_range( $args['date'], $item['date_start'], $item['finish_date'] ) ) {
					$item_reason      = $item['reasons'][0];
					$item_reason_name = $item_reason->name;
					$item_reason_id   = $item_reason->term_id;
					$reason_color     = carbon_get_term_meta( $item_reason_id, 'reason_color' );
					$_attr            = "style='background:$reason_color;'";
					echo "<div $_attr class='days-table-value-absence'>Відсутність через: <br> '$item_reason_name'</div>";
				}
			}
		}
	}
}

function the_preload(): void {
    if(is_single()){
        return;
    }
	?>
    <div class="preload">
        <div class="preload-content">
    <svg width="303" height="141" viewBox="0 0 303 141" fill="none" xmlns="http://www.w3.org/2000/svg" class="wm_logo">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5065 0L1.72559 20.1213H20.0108L12.5065 0Z"
                      fill="#414C9E"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.5063 0L29.757 46.1107L47.0068 0H12.5063Z"
                      fill="#7A87F8"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M299.291 72.0998L288.466 92.2206L280.962 72.0998H299.291Z" fill="#414C9E"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M81.5073 0L98.758 46.1107L116.008 0H81.5073Z"
                      fill="#2E357C"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M81.507 0L64.2568 46.1107H98.7577L81.507 0Z"
                      fill="#38419A"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M29.7568 46.1104H64.2573L47.0071 92.2207L29.7568 46.1104Z" fill="#5C6DF9"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M64.2568 46.1104H98.7573L81.5071 92.2207L64.2568 46.1104Z" fill="#414C9E"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M81.5068 92.2207H47.0063L64.2566 46.1104L81.5068 92.2207Z" fill="#5261DF"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M254.009 0L236.759 46.1107H271.259L254.009 0Z"
                      fill="#7C84C6"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M236.759 46.1104H271.259L254.009 92.2207L236.759 46.1104Z" fill="#6F77B1"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M288.51 92.2207H254.009L271.259 46.1104L288.51 92.2207Z" fill="#636B9E"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M116.008 0L98.7578 46.1107H133.258L116.008 0Z"
                      fill="#38419A"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M47.0071 0L29.7568 46.1107H64.2573L47.0071 0Z"
                      fill="#6A79F8"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M150.508 0L167.758 46.1107L185.008 0H150.508Z"
                      fill="#C9085F"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M150.508 0L133.257 46.1107H167.758L150.508 0Z"
                      fill="#AA0959"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M98.7578 46.1104H133.258L116.008 92.2207L98.7578 46.1104Z" fill="#414C9E"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M133.258 46.1104H167.759L150.509 92.2207L133.258 46.1104Z" fill="#890A50"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M150.508 92.2207H116.008L133.258 46.1104L150.508 92.2207Z" fill="#6B0341"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M185.008 0L167.758 46.1107H202.259L185.008 0Z"
                      fill="#2E357C"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M219.508 0L236.759 46.1107L254.009 0H219.508Z"
                      fill="#868FCB"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M219.509 0L202.259 46.1107H236.759L219.509 0Z"
                      fill="#38419A"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M167.758 46.1104H202.259L185.008 92.2207L167.758 46.1104Z" fill="#4652B0"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M202.259 46.1104H236.759L219.509 92.2207L202.259 46.1104Z" fill="#414C9E"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M219.509 92.2207H185.009L202.259 46.1104L219.509 92.2207Z" fill="#7389EA"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M33.3792 118.126L26.0478 139.043L18.5009 118.126H16.7327L9.09949 139.043L1.76815 118.126H0L8.19385 140.092H10.0051L17.6383 119.426L25.0986 140.092H26.9103L35.1473 118.126H33.3792Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M50.974 118.713C45.3677 118.713 40.9257 122.024 40.4082 127.977H61.281C61.281 121.857 56.4509 118.713 50.974 118.713ZM50.9742 117.54C58.3056 117.54 63.0062 122.151 63.0062 129.151H40.4085C40.8397 136.151 44.9367 139.379 50.9742 139.379C55.3299 139.379 58.9093 137.283 59.9443 134.181H61.885C60.9362 137.828 56.8824 140.595 50.9742 140.595C43.4704 140.595 38.856 136.151 38.856 129.109C38.856 122.067 43.5566 117.54 50.9742 117.54Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M80.2991 139.379C74.089 139.379 69.8627 135.187 69.8627 129.025C69.8627 122.905 74.1322 118.713 80.3422 118.713C86.5523 118.713 90.7786 122.989 90.7786 129.025C90.7786 135.145 86.5092 139.379 80.2991 139.379ZM80.3424 117.539C76.4611 117.539 72.3642 118.629 69.8629 123.743V109.659H68.3535V140.092H69.8629V134.223C72.1486 139.211 76.3749 140.595 80.3424 140.595C87.6738 140.595 92.3744 136.025 92.3744 129.025C92.3744 122.067 87.6738 117.539 80.3424 117.539Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M96.8169 129.738H111.35V128.648H96.8169V129.738Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M148.567 117.54C143.047 117.54 139.252 119.468 137.614 123.492C136.105 119.007 134.077 117.54 129.722 117.54C124.158 117.54 120.406 119.468 118.725 123.492V118.126H117.258V140.092H118.725V127.307C118.725 122.527 123.167 118.713 128.385 118.713C134.12 118.713 136.622 121.648 136.622 127.516V140.092H138.088V127.307C138.088 122.234 142.53 118.713 148.266 118.713C154.044 118.713 155.899 121.648 155.899 127.516V140.092H157.365V125.504C157.365 119.887 154.691 117.54 148.567 117.54Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M174.744 139.672C168.448 139.672 164.135 135.438 164.135 129.193C164.135 122.947 168.448 118.965 174.744 118.965C180.998 118.965 185.31 122.905 185.31 129.193C185.31 135.481 180.998 139.672 174.744 139.672ZM174.744 117.791C167.327 117.791 162.583 122.067 162.583 129.193C162.583 132.379 163.489 135.061 165.171 137.074C167.327 139.505 170.604 140.846 174.744 140.846C179.057 140.846 182.421 139.421 184.534 136.78C186.086 134.852 186.906 132.294 186.863 129.193C186.863 122.067 182.162 117.791 174.744 117.791Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M204.933 128.732L198.723 127.81C194.927 127.307 192.426 126.259 192.426 123.282C192.426 120.432 195.488 118.504 200.318 118.504C205.493 118.504 208.814 120.39 209.245 123.282H210.927C210.841 120.012 207.391 117.456 200.793 117.456C194.065 117.456 190.787 119.803 190.787 123.45C190.787 126.929 192.685 128.355 197.903 129.067L204.933 130.073C208.167 130.576 210.194 131.918 210.194 134.517C210.194 137.87 206.701 139.756 200.965 139.756C195.574 139.756 192.167 137.661 191.779 134.433H190.184C190.442 138.289 193.936 140.93 200.664 140.93C208.339 140.93 211.833 138.666 211.833 134.223C211.833 130.827 209.978 129.444 204.933 128.732Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M235.422 131.33C235.422 136.193 230.721 139.588 224.468 139.588C220.242 139.588 217.396 137.409 217.396 134.517C217.396 130.576 220.759 130.031 226.107 129.235C230.247 128.606 233.266 128.941 235.422 127.013V131.33ZM239.001 138.834C236.931 138.834 237.017 137.409 237.017 135.816V125.336C237.017 122.947 236.931 121.48 235.81 120.096C234.257 118.294 231.756 117.54 227.487 117.54C220.457 117.54 216.878 119.929 216.921 124.121H218.43C218.43 120.809 221.492 118.713 227.271 118.713C232.489 118.713 235.422 120.348 235.422 123.492C235.422 127.6 232.101 127.264 226.279 128.061C222.312 128.564 219.595 128.983 218.042 130.073C216.49 131.205 215.843 132.546 215.843 134.432C215.843 138.457 218.517 140.595 224.209 140.595C228.565 140.595 233.222 139.421 235.422 135.397C235.422 139.672 236.026 140.091 237.837 140.091C238.656 140.091 239.476 139.882 239.691 139.84V138.666C239.519 138.709 239.217 138.834 239.001 138.834Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M244.91 140.091H246.376V118.126H244.91V140.091Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M244.91 112.467H246.376V109.659H244.91V112.467Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M264.144 139.421C257.891 139.421 253.535 135.229 253.535 128.941C253.535 122.653 258.02 118.713 264.058 118.713C268.586 118.713 272.036 120.641 273.157 124.037H274.882C274.278 120.348 270.052 117.54 264.144 117.54C256.683 117.54 251.983 121.815 251.983 128.941C251.983 136.067 256.683 140.595 264.144 140.595C270.181 140.595 274.58 137.241 275.098 133.05H273.459C272.726 136.57 268.888 139.421 264.144 139.421Z"
                      fill="white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M298.731 131.33C298.731 136.193 294.031 139.588 287.777 139.588C283.551 139.588 280.704 137.409 280.704 134.517C280.704 130.576 284.068 130.031 289.416 129.235C293.556 128.606 296.575 128.941 298.731 127.013V131.33ZM303 138.666C302.828 138.709 302.526 138.834 302.31 138.834C300.24 138.834 300.326 137.409 300.326 135.816V125.336C300.326 122.947 300.24 121.48 299.119 120.096C297.566 118.294 295.065 117.54 290.796 117.54C283.766 117.54 280.187 119.929 280.23 124.121H281.74C281.74 120.809 284.801 118.713 290.58 118.713C295.798 118.713 298.731 120.348 298.731 123.492C298.731 127.6 295.41 127.264 289.588 128.061C285.621 128.564 282.904 128.983 281.351 130.073C279.799 131.205 279.152 132.546 279.152 134.432C279.152 138.457 281.825 140.595 287.518 140.595C291.874 140.595 296.531 139.421 298.731 135.397C298.731 139.672 299.334 140.091 301.146 140.091C301.965 140.091 302.784 139.882 303 139.84V138.666Z"
                      fill="white"/>
            </svg>
        </div>
    </div>
	<?php
}