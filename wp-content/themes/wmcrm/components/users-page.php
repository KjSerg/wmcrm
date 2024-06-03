<?php
function the_users_page() {
	$user_id  = get_current_user_id();
	$is_admin = is_current_user_admin();
	if(!$is_admin){
		die();
	}
	$var                   = variables();
	$set                   = $var['setting_home'];
	$assets                = $var['assets'];
	$url                   = $var['url'];
	$url_home              = $var['url_home'];
	$user                  = get_user_by( 'id', $user_id );
	get_header();
	?>
    <section class="section users-section" id="users-section">
        <div class="container">
            <div class="users-container">
                <div class="users-head">
                    <div class="users-title">
                        Люди (<?php echo count_all_users(); ?>) <a href="#new-user" class="button-add add-user modal-open">+</a>
                    </div>
                </div>
                <div class="users-table">
                    <div class="users-table-head">
                        <div class="users-table-column"></div>
                        <div class="users-table-column"></div>
                        <div class="users-table-column"></div>
                        <div class="users-table-column"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
	<?php
	get_footer();
}