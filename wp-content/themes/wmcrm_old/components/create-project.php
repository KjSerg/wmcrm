<?php
function the_create_project_page() {
	get_header();
	$users = get_users();
	$tags  = get_terms( array(
		'taxonomy'   => 'tags',
		'hide_empty' => false,
	) );
	?>
    <section class="section section-create">
        <div class="container">
            <div class="title">
                Створити проект
            </div>
            <form class="form form-js create-form" id="create-form" method="post">
                <input type="hidden" name="action" value="create_new_project">
                <input type="hidden" name="text" value="" class="value-field">
                <label class="form-group">
                    <input type="text" name="title" required placeholder="Введіть назву проєкта">
                </label>
				<?php if ( $users ): ?>
                    <label class="form-group">
                        <select name="responsible[]" multiple class="selectric">
                            <option disabled>Відповідальні персони</option>
							<?php foreach ( $users as $user ): ?>
                                <option value="<?php echo esc_attr( $user->ID ) ?>">
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
							<?php foreach ( $tags as $tag ): ?>
                                <option value="<?php echo esc_attr( $tag->term_id ) ?>">
									<?php echo esc_html( $tag->name ) ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </label>
				<?php endif; ?>
                <div id="editor" class="text"></div>
                <div class="text-editor-list"><?php the_user_select_list(); ?></div>
                <div class="form-buttons">
                    <button class="form-button">
                        Створити
                    </button>
                </div>
            </form>
        </div>
    </section>
	<?php
	get_footer();
}