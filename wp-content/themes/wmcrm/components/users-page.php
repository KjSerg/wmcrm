<?php
function the_users_page() {
	$user_id  = get_current_user_id();
	$is_admin = is_current_user_admin();
	if ( ! $is_admin ) {
		die();
	}
	get_header();
	$var        = variables();
	$set        = $var['setting_home'];
	$assets     = $var['assets'];
	$url        = $var['url'];
	$url_home   = $var['url_home'];
	$user       = get_user_by( 'id', $user_id );
	$avatar     = carbon_get_user_meta( $user_id, 'avatar' );
	$avatar     = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $user_id );
	$user_tel   = carbon_get_user_meta( $user_id, 'user_tel' );
	$user_email = $user->user_email;
	?>
    <section class="section users-section" id="users-section">
        <div class="container">
            <div class="users-container">
                <div class="users-head">
                    <div class="users-title">
                        Люди (<?php echo count_all_users(); ?>)
                        <a href="#new-user" class="button-add add-user modal-open">+</a>
                    </div>
                </div>
                <div class="users-table">
                    <div class="users-table-head">
                        <div class="users-table-column">
                            <div class="users-table__title">Працівник</div>
                        </div>
                        <div class="users-table-column">
                            <div class="users-table__title">Посада</div>
                        </div>
                        <div class="users-table-column">
                            <div class="users-table__title">Контакти</div>
                        </div>
                        <div class="users-table-column">
                            <div class="users-table__title text-align-right">Дія</div>
                        </div>
                    </div>
                    <div class="users-table-body">
						<?php if ( $user ): ?>
                            <div class="users-table-body-row">
                                <div class="users-table-column">
                                    <div class="users-table-item">
                                        <a href="<?php echo $avatar; ?>"
                                           class="users-table-item__avatar modal-open">
                                            <img class="cover" src="<?php echo $avatar; ?>" alt="">
                                        </a>
                                        <a href="<?php echo get_author_posts_url( $user_id ) ?>"
                                           class="users-table-item__name link-js">
											<?php echo $user->display_name; ?>
                                        </a>
										<?php the_user_status( $user_id ); ?>
                                    </div>
                                </div>
                                <div class="users-table-column">
                                    <div class="users-table__position">
										<?php echo carbon_get_user_meta( $user_id, 'position' ) ?: "Посада відсутня" ?>
                                    </div>
                                </div>
                                <div class="users-table-column">
									<?php the_user_contacts( $user_id ); ?>
                                </div>
                                <div class="users-table-column"></div>
                            </div>
						<?php endif; ?>
						<?php if ( $users = get_active_users() ): foreach ( $users as $_user ):
							the_user_row( $_user );
						endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
	<?php
	get_footer();
}