<?php
$var      = variables();
$set      = $var['setting_home'];
$assets   = $var['assets'];
$url      = $var['url'];
$url_home = $var['url_home'];
$user_id  = get_current_user_id();
if ( ! $user_id ) {
	header( 'Location: ' . $url );
	die();
}
$route    = $_GET['route'] ?? '';
$is_admin = is_current_user_admin();
if ( $route == 'edit' && $is_admin ) {
	the_edit_project_page();
	die();
}
get_header();
$id             = get_the_ID();
$isLighthouse   = isLighthouse();
$size           = isLighthouse() ? 'thumbnail' : 'full';
$parent_project = wp_get_post_parent_id( $id );
$children       = get_children_projects( $id );
$post_status    = get_post_status( $id );
$is_archive     = $post_status == 'archive';
$logo           = carbon_get_theme_option( 'logo' );
$permalink      = get_the_permalink();
$project_tags   = get_the_terms( $id, 'tags' ) ?: array();
if ( $is_admin = is_current_user_admin() ):
	?>
    <section class="section section-project-panel">
        <div class="container">
            <div class="section-container">
                <div class="project-management-wrapper">
                    <a href="<?php echo $permalink . '?route=edit' ?>"
                       class="button button__create element-center link-js">
                        Редагувати проєкт
                    </a>
                </div>
                <div class="section-project-status" data-id="<?php echo $id; ?>">
                    <div class="section-project-status-open project-status__item">
                        <div class="project-status__text">В роботі</div>
						<?php if ( ! $is_archive ): ?>
                            <div class="project-rabbit">
                                <img src="<?php echo $assets . 'img/favicon.png' ?>" alt="">
                            </div>
						<?php endif; ?>
                    </div>
                    <div class="section-project-status-archive project-status__item">
                        <div class="project-status__text">Закрито</div>
						<?php if ( $is_archive ): ?>
                            <div class="project-rabbit">
                                <img src="<?php echo $assets . 'img/favicon.png' ?>" alt="">
                            </div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section section-costs">
    <div class="container">
        <div class="section-container">
            <div class="section-costs-container">
                <a href="#" data-id="<?php echo $id ?>" class="costs-button play button">
                    <span class="costs-button-icon play-icon"><?php _s( _i( 'play' ) ) ?></span>
                    <span class="costs-button-icon pause-icon"><?php _s( _i( 'pause' ) ) ?></span>
                    <span class="costs-button-hour">00</span>
                    <span class="costs-button-minutes">00</span>
                    <span class="costs-button-seconds">00</span>
                </a>
                <div data-id="<?php echo $id ?>" class="costs-sum title">00:00</div>
            </div>
        </div>
    </div>
</section>

<section class="section section-project">
    <div class="container">
        <div class="section-project-wrap">
            <div class="title ">
				<?php echo get_the_title(); ?>
            </div>
            <div class="section-project-head">
				<?php if ( $parent_project ): ?>
                    <a href="<?php echo get_the_permalink( $parent_project ) ?>" class="section-project-parent link-js">
                        <span class="icon"><?php _s( _i( 'arr' ) ) ?></span>
						<?php echo get_the_title( $parent_project ) ?>
                    </a>
				<?php endif; ?>
				<?php if ( $children ): ?>
                    <div class="section-project-children">
						<?php foreach ( $children as $child_id ): ?>
                            <a href="<?php echo get_the_permalink( $child_id ) ?>"
                               class="section-project-parent link-js">
                                <span class="icon"><?php _s( _i( 'arr' ) ) ?></span>
								<?php echo get_the_title( $child_id ) ?>
                            </a>
						<?php endforeach; ?>
                    </div>
				<?php endif; ?>
                <div class="row">
                    <div class="section-project-date">
                        Дата створення: <?php echo get_the_date( 'd.m.Y H:i', $id ); ?>
                    </div>
					<?php if ( $project_tags ): ?>
                        <div class="project-tags">
							<?php foreach ( $project_tags as $tag ): ?>
                                <a href="<?php echo get_term_link( $tag->term_id ) ?>"><?php echo $tag->name; ?></a>
							<?php endforeach; ?>
                        </div>
					<?php endif; ?>
                </div>


            </div>
            <div class="section-project-content text">
				<?php
				ob_start( "replace_url" );
				the_post();
				the_content();
				ob_end_flush();
				?>
            </div>
        </div>
    </div>
</section>

<section class="section section-comments">
    <div class="container">
        <form class="section-comments-form comment-form form-js" method="post" id="comment-form">
            <input type="hidden" name="action" value="new_comment">
            <input type="hidden" name="project_id" value="<?php echo $id; ?>">
            <input type="hidden" name="comment_id" class="comment-field-id" value="">
            <input type="hidden" name="text" value="" class="value-field">
            <div id="editor" class="text editor-field"></div>
            <div class="text-editor-list"><?php the_user_select_list(); ?></div>
            <div class="form-buttons section-comments-form-buttons">
                <button class="form-button">Надіслати</button>
            </div>
        </form>
        <div class="section-comments-content">
			<?php
			the_comments( $id );
			?>
        </div>
    </div>
</section>


<?php get_footer(); ?>
