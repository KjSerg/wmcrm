<?php
function the_profile(): void {
	get_header();
	$user_id               = get_current_user_id();
	$var                   = variables();
	$set                   = $var['setting_home'];
	$assets                = $var['assets'];
	$url                   = $var['url'];
	$url_home              = $var['url_home'];
	$user                  = get_user_by( 'id', $user_id );
	$name                  = $user->display_name;
	$user_email            = $user->user_email;
	$user_name             = $user->user_firstname;
	$user_lastname         = $user->user_lastname;
	$telegram_id           = carbon_get_user_meta( $user_id, 'telegram_id' );
	$telegram_image        = carbon_get_user_meta( $user_id, 'telegram_image' );
	$position              = carbon_get_user_meta( $user_id, 'position' );
	$user_tel              = carbon_get_user_meta( $user_id, 'user_tel' );
	$birthday              = carbon_get_user_meta( $user_id, 'birthday' );
	$avatar                = carbon_get_user_meta( $user_id, 'avatar' );
	$upload_avatar         = $avatar;
	$comment_notification  = carbon_get_user_meta( $user_id, 'comment_notification' );
	$project_notification  = carbon_get_user_meta( $user_id, 'project_notification' );
	$birthday_notification = carbon_get_user_meta( $user_id, 'birthday_notification' );
	$telegram_notification = carbon_get_user_meta( $user_id, 'telegram_notification' );
	$email_notification    = carbon_get_user_meta( $user_id, 'email_notification' );
	$avatar                = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $user_id );
	$BOT_USERNAME          = carbon_get_theme_option( 'telegram_bot_name' );
	$BOT_TOKEN             = carbon_get_theme_option( 'telegram_token' );
	$REDIRECT_URI          = $url;
    if($user_id == 4){
        var_dump(get_next_work_timestamp($user_id));
        var_dump(is_working_hours($user_id));
    }
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	?>
    <section class="section profile-section">
        <div class="container">
            <div class="profile-head">
                <div class="profile-head-user">
                    <a class="profile-head-user__avatar modal-open" href="#avatar-modal-js">
                        <img src="<?php echo $avatar; ?>" alt="">
                    </a>
                    <div class="profile-head-user__name">
						<?php echo $name; ?>
                    </div>
                </div>
                <div class="profile-head-position">
					<?php echo $position ?: 'Посада'; ?>
                </div>
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
                <div class="profile-head-controls">
                    <a href="<?php echo wp_logout_url( home_url() ); ?>" class="button profile-head__button">Вийти</a>
                </div>
            </div>
            <form method="post" id="profile-form" novalidate
                  class="profile-form form form-js">
                <input type="hidden" name="action" value="change_user_data">
                <div class="row">
                    <label class="form-group form-group--third">
                        <span class="form-group__title">Прізвище</span>
                        <input type="text" name="user_lastname" required=""
                               value="<?php echo $user_lastname; ?>"
                               placeholder="Прізвище">
                    </label>
                    <label class="form-group form-group--third">
                        <span class="form-group__title">Імʼя</span>
                        <input type="text" name="user_firstname"
                               required=""
                               value="<?php echo $user_name; ?>"
                               placeholder="Ім’я">
                    </label>
                    <label class="form-group form-group--third">
                        <span class="form-group__title">Email</span>
                        <input type="email" name="email"
                               data-reg="[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])"
                               required="" value="<?php echo $user_email; ?>"
                               placeholder="Email">
                    </label>
                </div>
                <div class="row">
                    <label class="form-group form-group--third">
                        <span class="form-group__title">Телефон</span>
                        <input type="tel" name="phone" required=""
                               value="<?php echo $user_tel; ?>"
                               placeholder="Номер телефону">
                    </label>
                    <label class="form-group form-group--third">
                        <span class="form-group__title">Посада</span>
                        <input type="text" name="position"
                               required=""
                               value="<?php echo $position; ?>"
                               placeholder="Посада">
                    </label>
                    <label class="form-group form-group--third">
                        <span class="form-group__title">День народження</span>
                        <input type="text" name="birthday"
                               class="date-input"
                               readonly
                               value="<?php echo $birthday ?: '01-01-1995'; ?>"
                               placeholder="День народження">
                    </label>
                    <label class="form-group " style="width: 100%">
                        <span class="form-group__title">Головна сторінка</span>
                        <select name="main_page" class="selectric">
                            <option value="">
                                Зробіть вибір
                            </option>
                            <option value="https://crmwm.web-mosaica.top/discussion/">
                                Головна
                            </option>
                            <option value="https://crmwm.web-mosaica.top/projects/">
                                Задачі
                            </option>
                            <option value="https://crmwm.web-mosaica.top/?route=users">
                                Люди
                            </option>
                        </select>
                    </label>
                </div>
                <div class="profile-form-controls">
                    <button class="button profile-form__button" type="submit">
                        Зберегти зміни
                    </button>
                </div>
            </form>
            <form method="post" class="profile-notifications form-js" id="profile-notifications">
                <input type="hidden" name="action" value="change_user_notifications">
                <div class="switchers">
                    <div class="switcher">
                        <div class="switcher__text">Сповіщення про новий проєкт</div>
                        <label class="switch">
                            <input type="checkbox" name="project_notification" value="yes"
								<?php echo $project_notification ? 'checked' : '' ?>
                                   class="switch-input">
                            <span class="switch-element"></span>
                        </label>
                    </div>
                    <div class="switcher">
                        <div class="switcher__text">Сповіщення про новий коментар або згадка в коментарі</div>
                        <label class="switch">
                            <input type="checkbox" name="comment_notification" value="yes"

								<?php echo $comment_notification ? 'checked' : '' ?>
                                   class="switch-input">
                            <span class="switch-element"></span>
                        </label>
                    </div>
                    <div class="switcher">
                        <div class="switcher__text">Сповіщення про новий дні народження (бета)</div>
                        <label class="switch">
                            <input type="checkbox" name="birthday_notification" value="yes"
								<?php echo $birthday_notification ? 'checked' : '' ?>
                                   class="switch-input">
                            <span class="switch-element"></span>
                        </label>
                    </div>
                    <div class="switcher">
                        <label class="switcher__text">
                            Телеграм сповіщення
                        </label>
                        <label class="switch">
                            <input type="checkbox" name="telegram_notification"
								<?php echo $telegram_notification ? 'checked' : '' ?>
                                   value="yes" class="switch-input">
                            <span class="switch-element"></span>
                        </label>

                    </div>
                    <div class="switcher">
                        <div class="switcher__text">Email сповіщення</div>
                        <label class="switch">
                            <input type="checkbox" name="email_notification"
								<?php echo $email_notification ? 'checked' : '' ?>
                                   value="yes" class="switch-input">
                            <span class="switch-element"></span>
                        </label>
                    </div>
                </div>
				<?php if ( ! $telegram_id ): ?>
                    <script src="https://telegram.org/js/telegram-widget.js?2"
                            data-telegram-login="<?= $BOT_USERNAME ?>" data-size="medium"
                            data-auth-url="<?= $REDIRECT_URI ?>" data-request-access="write"></script>
				<?php endif; ?>
                <div class="row">
                    <label class="form-group form-group--half">
                        <span class="form-group__title">Початок роботи телеграм сповіщень</span>
                        <input type="time" name="telegram_start" value="<?php echo carbon_get_user_meta($user_id, 'telegram_start') ?: "09:00:00" ?>">
                    </label>
                    <label class="form-group form-group--half">
                        <span class="form-group__title">Закінчення роботи телеграм сповіщень</span>
                        <input type="time" name="telegram_finish" required="" value="<?php echo carbon_get_user_meta($user_id, 'telegram_finish') ?: "19:00:00" ?>">
                    </label>
                </div>
            </form>
        </div>
    </section>
    <div class="dialog-window avatar-modal" id="avatar-modal-js">
        <form class="user-avatar-form form-js <?php echo $upload_avatar ? 'uploaded-avatar' : ''; ?>"
              method="post"
              novalidate
              enctype="multipart/form-data"
              id="user-avatar-form">
            <input type="hidden" name="action" value="change_user_avatar">
            <div class="avatar-modal-image">
                <img src="<?php echo $avatar; ?>" alt="">
            </div>
            <div class="row">
                <a href="#" class="remove-avatar button">
                    Видалити аватар
                </a>
                <label class="button">
                    Вибрати аватар
                    <input type="file"
                           class="upload-avatar"
                           style="display:none;"
                           name="upfile[]"
                           accept="image/png, image/jpeg, image/heic"
                    >
                </label>
            </div>
        </form>
    </div>
	<?php
	get_footer();
}