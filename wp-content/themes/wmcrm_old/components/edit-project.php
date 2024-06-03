<?php
function the_edit_project_page() {
	get_header();
	$users        = get_users();
	$id           = get_the_ID();
	$post_status  = get_post_status( $id );
	$is_archive   = $post_status == 'archive';
	$logo         = carbon_get_theme_option( 'logo' );
	$permalink    = get_the_permalink();
	$title        = get_the_title();
	$text         = get_content_by_id( $id );
	$users_to_id  = carbon_get_post_meta( $id, 'project_users_to_id' );
	$project_tags = get_the_terms( $id, 'tags' ) ?: array();
	$tags         = get_terms( array(
		'taxonomy'   => 'tags',
		'hide_empty' => false,
	) );
	if ( $users_to_id ) {
		$users_to_id = explode( ',', $users_to_id );
	} else {
		$users_to_id = array();
	}
	?>

    <section class="section section-create">
        <div class="container">
            <div class="title">
                Змінити проект
            </div>
            <form class="form form-js create-form" id="create-form" method="post">
                <input type="hidden" name="action" value="create_new_project">
                <input type="hidden" name="project_id" value="<?php echo $id; ?>">
                <input type="hidden" name="text" value="<?php echo $text; ?>" class="value-field">
                <label class="form-group">
                    <input type="text" name="title" required
                           value="<?php echo $title; ?>"
                           placeholder="Введіть назву проєкта">
                </label>
				<?php if ( $users ): ?>
                    <label class="form-group">
                        <select name="responsible[]" multiple class="selectric">
                            <option disabled>Відповідальні персони</option>
							<?php foreach ( $users as $user ):
								$attr = in_array( $user->ID, $users_to_id ) ? 'selected' : '';
								?>
                                <option value="<?php echo esc_attr( $user->ID ) ?>" <?php echo esc_attr( $attr ) ?>>
									<?php echo esc_html( $user->display_name ) ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </label>
				<?php endif; ?>
				<?php if ( $tags ): ?>
                    <label class="form-group">
                        <select name="tags[]" multiple class="selectric">
                            <option disabled>Теги</option>
							<?php foreach ( $tags as $tag ):
								$attr = in_array( $tag->term_id, $project_tags ) ? 'selected' : '';
								?>
                                <option value="<?php echo esc_attr( $tag->term_id ) ?>" <?php echo $attr; ?>>
									<?php echo esc_html( $tag->name ) ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </label>
				<?php endif; ?>
                <div id="editor" class="text"><?php echo $text; ?></div>
                <div class="text-editor-list"><?php the_user_select_list(); ?></div>
                <div class="form-buttons">
                    <button class="form-button">
                        Редагувати
                    </button>
                </div>
            </form>
        </div>
    </section>
	<?php
	get_footer();
}