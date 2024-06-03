<?php

function the_user_select_list() {
	$users = get_users();
	if ( $users ):
		?>
        <select class="selectric select-user-quill-js" name="user">
            <option disabled selected>Оберіть</option>
			<?php foreach ( $users as $user ): ?>
                <option value="<?php echo $user->ID; ?>"><?php echo esc_html( $user->display_name ); ?></option>
			<?php endforeach; ?>
        </select>
	<?php
	endif;
}