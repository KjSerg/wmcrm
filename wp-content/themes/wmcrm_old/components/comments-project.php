<?php
function the_comments( $id ) {
	$link                    = get_the_permalink( $id );
	$default_posts_per_page  = get_option( 'posts_per_page' );
	$worksection_comment_ids = carbon_get_post_meta( $id, 'worksection_comment_ids' );
	$comment_ids             = carbon_get_post_meta( $id, 'project_comment_ids' );

	if ( $comment_ids || $worksection_comment_ids ) {

		$worksection_comment_ids = explode( ',', $worksection_comment_ids );
		$comment_ids             = explode( ',', $comment_ids );
		$comments_collection     = array_merge( $worksection_comment_ids, $comment_ids );
		$paged                   = $_GET['pagenumber'] ?? 1;
		$query                   = new WP_Query( array(
			'post_type'      => array( 'comments', 'discussion' ),
			'post_status'    => 'publish',
			'posts_per_page' => $default_posts_per_page,
			'post__in'       => $comments_collection,
			'paged'          => $paged,
		) );
		if ( $query->have_posts() ) {
			?>
            <div class="section-comments-list container-js"><?php
			while ( $query->have_posts() ) {
				$query->the_post();
				$comment_id = get_the_ID();
				the_comment_project( $comment_id );
			}
			?></div><?php
			?>
            <div class="pagination-wrapper pagination-js">
				<?php echo get_comments_next_link( $query->max_num_pages, $link ); ?>
            </div>
			<?php
		} else {
			?>
            <div class="title empty-title title-left">Обговорення відсутнє</div>
			<?php
		}
		wp_reset_postdata();
		wp_reset_query();

	} else {
		?>
        <div class="title empty-title title-left">Обговорення відсутнє</div>
		<?php
	}
}

function the_comment_project( $comment_id, $user_id = false ) {
	$user_id     = $user_id ?: get_current_user_id();
	$post_type   = get_post_type( $comment_id );
	$time        = carbon_get_post_meta( $comment_id, 'comment_worksection_date_added' ) ?: get_the_date( 'U', $comment_id );
	$name        = carbon_get_post_meta( $comment_id, 'worksection_user_name' );
	$email       = carbon_get_post_meta( $comment_id, 'worksection_user_email' );
	$is_service  = carbon_get_post_meta( $comment_id, 'discussion_is_service' );
	$author_id   = get_post_field( 'post_author', $comment_id );
	$user        = get_user_by( 'id', $author_id );
	$is_archive  = $post_type == 'comments';
	$author_test = ( ! $is_archive && $author_id == $user_id );
	if ( ! $name ) {
		$name = $user->display_name;
	}
	?>
    <div class="comment <?php echo $is_archive ? 'archive-comment' : ''; ?> <?php echo $is_service ? 'service-comment' : ''; ?>" id="comment-<?php echo $comment_id ?>">
        <div class="comment-head">
            <div class="comment-author">
				<?php echo $name ?>
            </div>
            <div class="comment-date">
				<?php echo date( 'd-m-Y H:i', $time );
				echo $is_archive ? ' [архів]' : ''; ?>
            </div>
			<?php if ( ! $is_service ): ?>
				<?php if ( $author_test ): ?>
                    <a href="#" data-id="<?php echo $comment_id ?>" class="comment-remove remove-btn comment-remove-js">
						<?php _s( _i( 'remove' ) ) ?>
                    </a>
                    <a href="#" data-id="<?php echo $comment_id ?>" class="comment-change change-btn comment-change-js">
						<?php _s( _i( 'edit' ) ) ?>
                    </a>
				<?php endif; ?>
			<?php else: ?>
                <div class="comment-content text">
					<?php echo replace_url( get_content_by_id( $comment_id ) ); ?>
                </div>
			<?php endif; ?>
        </div>
		<?php if ( ! $is_service ): ?>
            <div class="comment-content text">
				<?php echo replace_url( get_content_by_id( $comment_id ) ); ?>
            </div>
		<?php endif; ?>
    </div>
	<?php
}