<?php
global $wp_query;
$user_id  = get_current_user_id();
$var      = variables();
$set      = $var['setting_home'];
$assets   = $var['assets'];
$url      = $var['url'];
$url_home = $var['url_home'];
if ( ! $user_id ) {
	header( 'Location: ' . $url );
	die();
}
get_header();
$current_author_id = $wp_query->get_queried_object()->ID;
$user              = get_user_by( 'id', $current_author_id );
$name              = $user->display_name;
$user_email        = $user->user_email;
$user_name         = $user->user_firstname;
$user_lastname     = $user->user_lastname;
$avatar            = carbon_get_user_meta( $current_author_id, 'avatar' );
$avatar            = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $current_author_id );
$telegram_id       = carbon_get_user_meta( $current_author_id, 'telegram_id' );
$telegram_image    = carbon_get_user_meta( $current_author_id, 'telegram_image' );
$position          = carbon_get_user_meta( $current_author_id, 'position' );
$user_tel          = carbon_get_user_meta( $current_author_id, 'user_tel' );
$birthday          = carbon_get_user_meta( $current_author_id, 'birthday' );
?>

<section class="section profile-section">
    <div class="container">
        <div class="profile-head">
            <div class="profile-head-user">
                <div class="profile-head-user__avatar ">
                    <img src="<?php echo $avatar; ?>" alt="">
                </div>
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

            </div>
        </div>
        <div class="profile-form form ">
            <div class="row">
                <label class="form-group form-group--third">
                    <span class="form-group__title">Прізвище</span>
                    <input readonly type="text" name="user_lastname" required=""
                           value="<?php echo $user_lastname; ?>"
                           placeholder="Прізвище">
                </label>
                <label class="form-group form-group--third">
                    <span class="form-group__title">Імʼя</span>
                    <input readonly type="text" name="user_firstname"
                           required=""
                           value="<?php echo $user_name; ?>"
                           placeholder="Ім’я">
                </label>
                <label class="form-group form-group--third">
                    <span class="form-group__title">Email</span>
                    <input readonly type="text" name="email"
                           data-reg="[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])"
                           required="" value="<?php echo $user_email; ?>"
                           placeholder="Email">
                </label>
            </div>
            <div class="row">
                <label class="form-group form-group--third">
                    <span class="form-group__title">Телефон</span>
                    <input readonly type="tel" name="phone" required=""
                           value="<?php echo $user_tel; ?>"
                           placeholder="Номер телефону">
                </label>
                <label class="form-group form-group--third">
                    <span class="form-group__title">Посада</span>
                    <input readonly type="text" name="position"
                           required=""
                           value="<?php echo $position; ?>"
                           placeholder="Посада">
                </label>
                <label class="form-group form-group--third">
                    <span class="form-group__title">День народження</span>
                    <input readonly type="text" name="birthday"
                           value="<?php echo $birthday ?: '01-01-2000'; ?>"
                           placeholder="День народження">
                </label>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
