<?php
function the_create_project_page() {
	get_header();
	$users        = get_active_users();
	$id           = get_the_ID();
	$post_status  = get_post_status( $id );
	$is_archive   = $post_status == 'archive';
	$logo         = carbon_get_theme_option( 'logo' );
	$permalink    = get_the_permalink();
	$title        = get_the_title();
	$text         = get_content_by_id( $id );
	$users_to_id  = carbon_get_post_meta( $id, 'project_users_to_id' );
	$observer_id  = carbon_get_post_meta( $id, 'project_users_observer_id' );
	$project_tags = get_the_terms( $id, 'tags' ) ?: array();
	$statuses     = array(
		'publish' => 'В роботі',
		'archive' => 'Завершена',
		'pending' => 'В черзі'
	);
	$tags         = get_terms( array(
		'taxonomy'   => 'tags',
		'hide_empty' => false,
	) );
	if ( $users_to_id ) {
		$users_to_id = explode( ',', $users_to_id );
	} else {
		$users_to_id = array();
	}
	if ( $observer_id ) {
		$observer_id = explode( ',', $observer_id );
	} else {
		$observer_id = array();
	}
	?>

    <section class="section create-section">
        <div class="container">
            <div class="create-section-container">
                <div class="title">
                    Нова задача
                </div>
                <form class="form form-js create-form" id="create-form" method="post">
                    <input type="hidden" name="action" value="create_new_project">
                    <input type="hidden" name="text" value="" class="value-field">
                    <label class="form-group">
                        <span class="form-group__title"> Заголовок</span>
                        <input type="text" name="title" required
                               value=""
                               placeholder="Введіть назву проєкта">
                    </label>
                    <div class="form-group">
                        <span class="form-group__title">Опис задачі</span>
                        <div id="editor" class="text"></div>
                        <div class="text-editor-list"><?php the_user_select_list(); ?></div>
                    </div>
                    <div class="form-row">
						<?php if ( $users ): ?>
                            <label class="form-group half">
                                <span class="form-group__title">Спостерігачі</span>
                                <select name="observers[]" multiple class="selectric">
                                    <option disabled>Спостерігачі</option>
									<?php foreach ( $users as $user ):
										?>
                                        <option value="<?php echo esc_attr( $user->ID ) ?>" >
											<?php echo esc_html( $user->display_name ) ?>
                                        </option>
									<?php endforeach; ?>
                                </select>
                            </label>
                            <label class="form-group half">
                                <span class="form-group__title">Відповідальні особи</span>
                                <select name="responsible[]" multiple class="selectric">
                                    <option disabled>Відповідальні персони</option>
									<?php foreach ( $users as $user ):
										?>
                                        <option value="<?php echo esc_attr( $user->ID ) ?>" >
											<?php echo esc_html( $user->display_name ) ?>
                                        </option>
									<?php endforeach; ?>
                                </select>
                            </label>
						<?php endif; ?>
                    </div>
                    <div class="form-row">
						<?php if ( $statuses ): ?>
                            <label class="form-group half">
                                <span class="form-group__title">Статус задачі</span>
                                <select name="post_status" class="selectric">
									<?php foreach ( $statuses as $status => $str ): ?>
                                        <option value="<?php echo $status; ?>"><?php echo $str; ?></option>
									<?php endforeach; ?>
                                </select>
                            </label>
						<?php endif; ?>
						<?php if ( $tags ): ?>
                            <label class="form-group half">
                                <span class="form-group__title">Тип задачі</span>
                                <select name="tags[]" multiple class="selectric">
                                    <option disabled>Теги</option>
									<?php foreach ( $tags as $tag ):
										?>
                                        <option value="<?php echo esc_attr( $tag->term_id ) ?>" >
											<?php echo esc_html( $tag->name ) ?>
                                        </option>
									<?php endforeach; ?>
                                </select>
                            </label>
						<?php endif; ?>
                    </div>
                    <div class="form-buttons">
                        <button class="form-button button">
                            Створити
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
	<?php
	get_footer();
}