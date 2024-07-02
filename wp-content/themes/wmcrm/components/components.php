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
                $res = get_stopwatches( $timer_ID );
                $test = $res && isset($res['work']['string']);
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
                        if($test){
	                        if ( $sum_hour_arr = explode( ':', $res['work']['string'] ) ) {
		                        echo $sum_hour_arr[0] . ':' . $sum_hour_arr[1];
	                        } else {
		                        echo $res['work']['string'];
	                        }
                        }else{
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
                            Тривалість робочого дня: <span><?php echo $costs_sum_hour ?></span>
                        </div>
                        <div class="timer-pause-time">
                            Тривалість перерви: <span><?php echo $costs_sum_pause_hour ?></span>
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

function the_absences( $id = false ) {
	$id              = $id ?: get_the_ID();
	$current_user_id = get_current_user_id();
	$is_admin        = is_current_user_admin();
	$author_id       = get_post_field( 'post_author', $id );
	$test            = $author_id == $current_user_id;
	$test            = $test ?: $is_admin;
	if ( $test ):
		$user = get_user_by( 'id', $author_id );
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
				<?php if ( is_current_user_admin() ): ?>
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

