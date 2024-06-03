<?php
function the_project( $id = false ) {
	$id                  = $id ?: get_the_ID();
	$projects            = get_post_type_archive_link( 'projects' );
	$performer_id        = carbon_get_post_meta( $id, 'worksection_user_to_id' );
	$performer_name      = carbon_get_post_meta( $id, 'worksection_user_to_name' );
	$parent_id           = wp_get_post_parent_id( $id );
	$post_status         = get_post_status( $id );
	$post_status_string  = $post_status == 'archive' ? ' [Закрито]' : '';
	$project_users_to_id = carbon_get_post_meta( $id, 'project_users_to_id' );
	?>
    <div class="project-item" data-aos="fade-up" id="project-<?php echo $id ?>">
        <a href="<?php echo get_the_permalink( $id ) ?>" class="project-item-title link-js">
			<?php echo get_the_title( $id ) . $post_status_string; ?>
        </a>
		<?php if ( $project_users_to_id ): $project_users_to_id = explode( ',', $project_users_to_id ); ?>
            <div class="project-item-performers">
				<?php foreach ( $project_users_to_id as $_user_id ):
					if ( $u = get_user_by( 'id', $_user_id ) ):
						$worksection_id = carbon_get_user_meta( $_user_id, 'worksection_id' );
						$param = $worksection_id ? '?performer=' . $worksection_id : '?user=' . $_user_id;
						?>
                        <a href="<?php echo $projects . $param ?>"
                           class="project-item-performer link-js">
							<?php echo $u->display_name; ?>
                        </a>
					<?php
					endif;
				endforeach;
				?>
            </div>
		<?php else: ?>
			<?php if ( $performer_id ): ?>
                <div class="project-item-performers">
                    <a href="<?php echo $projects . '?performer=' . $performer_id ?>"
                       class="project-item-performer link-js">
						<?php echo $performer_name; ?>
                    </a>
                </div>
			<?php else: ?>
                <div class="project-item-performers"></div>
			<?php endif; ?>
		<?php endif; ?>

        <div class="project-item-date">
			<?php echo get_the_date( 'd.m.Y H:i', $id ); ?>
        </div>
    </div>
	<?php
}

function the_projects_page() {
	$type = $_GET['type'] ?? '';
	if ( $type == 'next_project_page' ) {
		global $wp_query;
		?>
        <span class="found-posts"><?php echo $wp_query->found_posts; ?></span>
        <div class="projects container-js" id="list-list">
			<?php
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					$id = get_the_ID();
					the_project();
				}
			} else {
				?>
                <div class="title title-left">Не знайдено</div>
				<?php
			}
			?>
        </div>
        <div class="pagination-wrapper pagination-js">
			<?php echo _get_next_link(); ?>
        </div>
		<?php
		die();
	}
}