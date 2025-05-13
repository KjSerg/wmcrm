<?php
//if(get_the_ID() == 3790){
//	set_projects_users();
//}
$string         = $_GET['string'] ?? '';
$comments_count = $_GET['comments_count'] ?? '';
$redirected     = $_GET['redirected'] ?? 'false';
$id             = get_the_ID();
if ( $string && $redirected == 'false' ) {
	$permalink  = get_the_permalink();
	$comment_id = get_comment_id_by_string( $string, $id );
	if ( $comment_id ) {
		var_dump( $comment_id );
		header( 'Location: ' . $permalink . "?comments_count=$comments_count&string=$string&redirected=true#comment-$comment_id" );
		die();
	}
}
$var             = variables();
$set             = $var['setting_home'];
$assets          = $var['assets'];
$url             = $var['url'];
$url_home        = $var['url_home'];
$user_id         = get_current_user_id();
$current_project = carbon_get_user_meta( $user_id, 'current_project' );
if ( ! $user_id ) {
	header( 'Location: ' . $url );
	die();
}
$worksection_id = carbon_get_user_meta( $user_id, 'worksection_id' );
$route          = $_GET['route'] ?? '';
$subtype        = $_GET['subtype'] ?? '';
$is_admin       = is_current_user_admin();
if ( $route == 'edit' && $is_admin ) {
	the_edit_project_page();
	die();
}
if ( $subtype !== 'modal' ) {
	get_header();
}
$title                     = get_the_title();
$isLighthouse              = isLighthouse();
$size                      = isLighthouse() ? 'thumbnail' : 'full';
$parent_project            = wp_get_post_parent_id( $id );
$children                  = get_children_projects( $id );
$post_status               = get_post_status( $id );
$is_archive                = $post_status == 'archive';
$logo                      = carbon_get_theme_option( 'logo' );
$permalink                 = get_the_permalink();
$project_tags              = get_the_terms( $id, 'tags' ) ?: array();
$projects_url              = get_post_type_archive_link( 'projects' );
$performers_id             = carbon_get_post_meta( $id, 'project_users_to_id' );
$worksection_performers_id = carbon_get_post_meta( $id, 'worksection_user_to_id' );
$observers_id              = carbon_get_post_meta( $id, 'project_users_observer_id' );
$performers_id             = $performers_id ? explode( ",", $performers_id ) : array();
$is_performer              = in_array( $user_id, $performers_id ) || $worksection_id == $worksection_performers_id;
$tags                      = get_the_terms( $id, 'tags' );
$edit_link                 = $permalink . '?route=edit';
$children                  = get_children( array( 'post_parent' => $id ) );
$post_parent_id            = get_post_parent( $id );
if ( $post_status == 'pending' ) {
	$edit_link = $permalink . '&route=edit';
}
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
?>

<div class="nav">
    <div class="container">
        <div class="nav-list">
			<?php if ( $post_parent_id ): ?>
                <a href="<?php echo get_the_permalink( $post_parent_id ); ?>" class="nav-list__item link-js">
                <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12"
                                        fill="none">
<path d="M0.174904 5.57196C-0.058285 5.80515 -0.058285 6.19475 0.174904 6.42853L5.55538 11.8228C5.79156 12.059 6.17458 12.059 6.41016 11.8228C6.64634 11.5866 6.64634 11.203 6.41016 10.9668L1.45709 5.99997L6.41076 1.0337C6.64694 0.796928 6.64694 0.41391 6.41076 0.177134C6.17458 -0.0590446 5.79156 -0.0590446 5.55598 0.177134L0.174904 5.57196Z"
      fill="#5C6DF9"/>
</svg></span>
                    До головної задачі
                </a>
			<?php else: ?>
                <a href="<?php echo $projects_url; ?>" class="nav-list__item link-js">
                <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12"
                                        fill="none">
<path d="M0.174904 5.57196C-0.058285 5.80515 -0.058285 6.19475 0.174904 6.42853L5.55538 11.8228C5.79156 12.059 6.17458 12.059 6.41016 11.8228C6.64634 11.5866 6.64634 11.203 6.41016 10.9668L1.45709 5.99997L6.41076 1.0337C6.64694 0.796928 6.64694 0.41391 6.41076 0.177134C6.17458 -0.0590446 5.79156 -0.0590446 5.55598 0.177134L0.174904 5.57196Z"
      fill="#5C6DF9"/>
</svg></span>
                    Назад до списку задач
                </a>
			<?php endif; ?>
        </div>
    </div>
</div>

<section class="section project-section project-section-head">
    <div class="container">
        <div class="project-section-wrapper">
            <div class="project-section-row">
				<?php if ( $subtype == 'modal' ): ?>
                    <a href="<?php echo $permalink; ?>" class="project-title link-js">
						<?php echo $title ?>
                    </a>
				<?php else: ?>
                    <div class="project-title">
						<?php echo $title ?>
                        <a href="<?php echo $permalink ?>" class="copy-link">
							<?php _s( _i( 'link' ) ) ?>
                        </a>
                    </div>
				<?php endif; ?>
                <div class="project-control">
                    <div class="project-control__time">
						<?php echo get_project_time( $id ); ?>
                    </div>
					<?php if ( ! $is_admin ): ?>
						<?php if ( $is_performer && $current_project != $id ): ?>
							<?php if ( $post_status == 'pending' || $post_status == 'publish' ): ?>
                                <a href="#" data-id="<?php echo $id ?>"
                                   data-title="<?php echo get_the_title( $id ) ?>"
                                   data-permalink="<?php echo get_the_permalink( $id ) ?>"
                                   class="project-control__user-button project-start button button-green ">
                                    Розпочати задачу
                                </a>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( $is_admin ): ?>
                        <a href="<?php echo $edit_link ?>" data-id="<?php echo $id ?>"
                           class="project-control__user-button project-edit button button-blue link-js">
                            Редагувати проект
                        </a>
						<?php if ( $is_archive ): ?>
                            <a href="#" data-id="<?php echo $id ?>"
                               class="project-control__user-button project-button-action project-open button button-red">
                                Відкрити проект
                            </a>
						<?php else: ?>
                            <a href="#" data-id="<?php echo $id ?>"
                               class="project-control__user-button project-button-action project-close button button-red">
                                Закрити проект
                            </a>
						<?php endif; ?>
					<?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<section class="section project-section project-section-content">
    <div class="container">
        <div class="project-section-wrapper">
            <div class="project-section-content-head">
                <div class="project-section-content-head__row">
                    <div class="project-users">
						<?php the_performers( $id ); ?>
                    </div>
                    <div class="project-users">
						<?php the_observers( $id ); ?>
						<?php the_project_author( $id ); ?>
                    </div>
                </div>
                <div class="project-section-content-head__bottom">
                    <div class="project-date">
						<?php echo get_the_date( 'd.m.Y H:i' ); ?>
                    </div>
					<?php the_post_status_html( $post_status, $id ); ?>
					<?php the_project_tags_html( $tags, get_terms( array(
						'taxonomy'   => 'tags',
						'hide_empty' => false,
					) ) ); ?>
                </div>
            </div>
            <div class="project-section-text text">
				<?php echo replaceUrl( get_content_by_id( $id ) ) ?>
            </div>
        </div>
    </div>
</section>

<?php
if ( $children ) {
	?>
    <section class="section section-projects project-section-children">
        <div class="container">
            <div class="projects">
				<?php
				foreach ( $children as $child ) {
					the_project( $child->ID, 'child project-item-inner' );
				}
				?>
            </div>
        </div>
    </section>
	<?php
}
?>

<section class="section section-comments project-section project-section-comments ">
    <div class="container">
        <form class="project-section-comments-form project-section-wrapper comment-form form-js"
              enctype="multipart/form-data"
              method="post"
              id="comment-form">
            <input type="hidden" name="action" value="new_comment">
            <input type="hidden" name="project_id" value="<?php echo $id; ?>">
            <input type="hidden" name="comment_id" class="comment-field-id" value="">
            <input type="hidden" name="text" value="" class="value-field">
            <div id="editor" data-project-id="<?php echo $id ?>" class="text editor-field"></div>
            <div class="text-editor-list"><?php the_user_select_list(); ?></div>
            <ul class="form-files-result"></ul>
            <div class="form-buttons section-comments-form-buttons">
                <button class="form-button button">Надіслати</button>
                <label class="form-files-label">
                    <span class="icon"><?php _s( _i( 'attach' ) ) ?></span>
                    <input type="file"
                           multiple
                           class="upload-files"
                           style="display:none;"
                           name="upfile[]"
                    >
                </label>
            </div>
        </form>
        <div class="section-comments-content ">
			<?php
			\WMCRM\core\Comments::render( $id );
			?>
        </div>
    </div>
</section>

<?php
if ( $subtype !== 'modal' ) {
	get_footer();
}
?>
