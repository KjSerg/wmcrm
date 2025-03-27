<?php
function the_comments( $id ): void {
	$comments_count          = filter_input( INPUT_GET, 'comments_count', FILTER_SANITIZE_NUMBER_INT );
	$link                    = get_the_permalink( $id );
	$default_posts_per_page  = get_option( 'posts_per_page' );
	$worksection_comment_ids = carbon_get_post_meta( $id, 'worksection_comment_ids' );
	$comment_ids             = carbon_get_post_meta( $id, 'project_comment_ids' );
	if ( $comment_ids || $worksection_comment_ids ) {
		$worksection_comment_ids = explode( ',', $worksection_comment_ids );
		$comment_ids             = explode( ',', $comment_ids );
		$comments_collection     = array_merge( $worksection_comment_ids, $comment_ids );
		$paged                   = filter_input( INPUT_GET, 'pagenumber', FILTER_SANITIZE_NUMBER_INT ) ?: 1;
		$query_args              = array(
			'post_type'      => array( 'comments', 'discussion' ),
			'post_status'    => 'publish',
			'posts_per_page' => $default_posts_per_page,
			'post__in'       => $comments_collection,
			'paged'          => $paged,
		);
		if ( $comments_count ) {
			$query_args['posts_per_page'] = (int) $comments_count;
		}
		$query = new WP_Query( $query_args );
		if ( $query->have_posts() ) {
			?>
            <div class="section-comments-list container-js"><?php
				while ( $query->have_posts() ) {
					$query->the_post();
					$comment_id = get_the_ID();
					the_comment_project( $comment_id, false, $id );
				}
				?>
            </div>
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

function the_comment_project( $comment_id, $user_id = false, $project_id = false ) {
	$paged       = filter_input( INPUT_GET, 'pagenumber', FILTER_SANITIZE_NUMBER_INT ) ?: 1;
	$paged       = intval( $paged );
	$user_id     = $user_id ?: get_current_user_id();
	$post_type   = get_post_type( $comment_id );
	$time        = carbon_get_post_meta( $comment_id, 'comment_worksection_date_added' ) ?: get_the_date( 'U', $comment_id );
	$name        = carbon_get_post_meta( $comment_id, 'worksection_user_name' );
	$email       = carbon_get_post_meta( $comment_id, 'worksection_user_email' );
	$is_service  = carbon_get_post_meta( $comment_id, 'discussion_is_service' );
	$author_id   = get_post_field( 'post_author', $comment_id );
	$user        = get_user_by( 'id', $author_id );
	$is_archive  = $post_type == 'comments';
	$author_test = ( ! $is_archive && $author_id == $user_id ) || is_current_user_admin();
	$avatar      = false;
	$is_read     = false;
	$users_read  = carbon_get_post_meta( $comment_id, 'discussion_read_users' );

	if ( $users_read ) {
		$users_read = explode( ',', $users_read );
		$is_read    = in_array( $user_id, $users_read );
	}
	if ( $user ) {
		$avatar = carbon_get_user_meta( $author_id, 'avatar' );
		$avatar = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $author_id );
	}
	if ( ! $name ) {
		$last_name  = $user->last_name;
		$first_name = $user->first_name;
		$name       = $last_name;
		if ( $first_name ) {
			$name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
		}
	}
	$cls                   = $is_read ? 'read' : 'unread';
	$discussion_files      = carbon_get_post_meta( $comment_id, 'discussion_files' );
	$discussion_edit_users = carbon_get_post_meta( $comment_id, 'discussion_edit_users' );
	$discussion_edit_users = $discussion_edit_users ? explode( ',', $discussion_edit_users ) : array();
	$copy_link_href        = get_the_permalink( $project_id );
	if ( $paged > 1 ) {
		$copy_link_href .= '?comments_count=-1';
	}
	$copy_link_href .= '#comment-' . $comment_id;
	?>
    <div class="comment project-section-wrapper <?php echo $is_archive ? 'archive-comment' : '';
	echo ' ' . $cls; ?> <?php echo $is_service ? 'service-comment' : ''; ?>"
		<?php if ( ! $is_read && ! $is_archive ): ?>
            data-reading-id="<?php echo $comment_id; ?>"
		<?php endif; ?>
         id="comment-<?php echo $comment_id ?>">
        <div class="comment-head">
            <div class="comment-author">
				<?php if ( $avatar ) {
					echo "<div class='comment-author__avatar'><img class='cover' src='$avatar' alt=''/></div>";
				} ?>
				<?php
				echo $name;
				if ( $discussion_edit_users ) {
					$discussion_edit_users_count = count( $discussion_edit_users );
					echo "<br> ✍️: ";
					foreach ( $discussion_edit_users as $user_index => $edit_user ) {
						if ( $edit_user = get_user_by( 'id', $edit_user ) ) {
							$user_index += 1;
							echo $edit_user->display_name;
							if ( $user_index < $discussion_edit_users_count ) {
								echo ', ';
							}
						}
					}
				}
				?>

            </div>
            <div class="comment-date">
				<?php echo date( 'd-m-Y H:i', $time );
				echo $is_archive ? ' [архів]' : ''; ?>
            </div>
			<?php if ( ! $is_service ): ?>
                <a href="<?php echo $copy_link_href ?>" class="copy-link comment-copy-link">
					<?php _s( _i( 'link' ) ) ?>
                </a>
				<?php if ( $author_test && ! $is_archive ): ?>

					<?php if ( $author_id == $user_id || is_current_user_admin() ): ?>
                        <a href="#" data-id="<?php echo $comment_id ?>"
                           class="comment-remove remove-btn comment-remove-js">
							<?php _s( _i( 'remove' ) ) ?>
                        </a>
					<?php endif; ?>
                    <a href="#" data-id="<?php echo $comment_id ?>" class="comment-change change-btn comment-change-js">
						<?php _s( _i( 'edit' ) ) ?>
                    </a>


				<?php endif; ?>
			<?php else: ?>
                <div class=" text">
					<?php echo replace_url( get_content_by_id( $comment_id ) ); ?>
                </div>
			<?php endif; ?>
        </div>
		<?php if ( ! $is_service ): ?>
            <div class="comment-content text">
				<?php echo replace_url( get_content_by_id( $comment_id ) ); ?>
            </div>
		<?php endif; ?>
		<?php if ( $discussion_files ): ?>
            <ul class="comment-files">
				<?php foreach ( $discussion_files as $discussion_file ):
					$file_url = $discussion_file['url'];
					if ( $file_url ) :
						$attachment_id = attachment_url_to_postid( $file_url );
						$attachment_file = get_attached_file( $attachment_id );
						?>
                        <li class="comment-file">
                            <div class="icon"><?php _s( _i( 'file' ) ) ?></div>
                            <a target="_blank"
                               href="<?php echo $file_url; ?>">
								<?php echo basename( $attachment_file ); ?>
                                (<?php echo getFileSize( $attachment_file ); ?>)
                            </a>
                        </li>
					<?php endif; endforeach; ?>
            </ul>
		<?php endif; ?>
    </div>
	<?php
}

function the_project_comment() {
	$user_id        = get_current_user_id();
	$id             = get_the_ID();
	$item_time      = get_the_date( 'H:i', $id );
	$item_date      = get_the_date( 'l j F', $id );
	$post_date_time = get_the_date( 'Y-m-d H:i:s' );
	$post_date      = get_the_date( 'd-m-Y' );
	$current_time   = current_time( 'timestamp' );
	$current_date   = date( 'd-m-Y', $current_time );
	$human_date     = human_time_diff( strtotime( $post_date ), current_time( 'timestamp' ) ) . ' назад';
	$formatted_date = $post_date;
	$color          = '#5C6DF9';
	if ( $current_date == $post_date ) {
		$formatted_date = 'сьогодні';
		$color          = '#6C3';
	} else {
		$diff = (int) abs( strtotime( $post_date ) - $current_time );
		if ( $diff >= DAY_IN_SECONDS && $diff <= ( DAY_IN_SECONDS * 2 ) ) {
			$formatted_date = 'вчора';
			$color          = '#FFE066';
		}
	}
	$project_id     = carbon_get_post_meta( $id, 'discussion_project_id' );
	$hush           = carbon_get_post_meta( $id, 'discussion_project_hush' );
	$post_type      = get_post_type( $project_id );
	$view_test      = true;
	if ( $post_type === 'costs' ) {
		$view_test = carbon_get_user_meta( $user_id, 'super_admin' );
	}
	if ( $view_test ):
		$is_read = false;
		$users_read = carbon_get_post_meta( $id, 'discussion_read_users' );
		if ( $users_read ) {
			$users_read = explode( ',', $users_read );
			$is_read    = in_array( $user_id, $users_read );
		}
		$check_cls             = $is_read ? 'read' : 'unread';
		$link                  = get_post_type_archive_link( 'discussion' );
		$comment_link          = ( $post_type == 'costs' ) ? get_post_type_archive_link( 'costs' ) : get_the_permalink( $project_id ) . '#comment-' . $id;
		$check_cls             .= $post_type == 'costs' ? ' change-user-time-item' : '';
		$costs_sum_hour_change = carbon_get_post_meta( $project_id, 'costs_sum_hour_change' );
		$costs_sum             = carbon_get_post_meta( $project_id, 'costs_sum_hour' );
		$res                   = $costs_sum;
		$string                = $res;
		if ( $sum_hour_arr = explode( ':', $costs_sum ) ) {
			if ( isset( $sum_hour_arr[0] ) ) {
				$res = $sum_hour_arr[0];
			}
			if ( isset( $sum_hour_arr[1] ) ) {
				$res .= ':' . $sum_hour_arr[1];
			}
		}
		if ( $res ) {
			$string = $costs_sum_hour_change ? $res . '⮕' . $costs_sum_hour_change : $res;
		}
		?>
        <div class="discussion-item <?php echo $check_cls; ?>"
			<?php if ( ! $is_read ): ?>
                data-reading-id="<?php echo $id; ?>"
			<?php endif; ?>
        >
            <div class="discussion-item-date">
                <div class="discussion-item-date__label"
                     style="background-color: <?php echo $color ?>;"><?php echo $formatted_date; ?></div>
                <div class="discussion-item__time"><?php echo $item_time; ?></div>
                <div class="discussion-item__check <?php echo $check_cls; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" fill="none">
                        <path d="M10.75 0.5C5.23622 0.5 0.75 4.98622 0.75 10.5C0.75 16.0138 5.23622 20.5 10.75 20.5C16.2638 20.5 20.75 16.0138 20.75 10.5C20.75 4.98622 16.2638 0.5 10.75 0.5ZM16.339 7.86842L9.948 14.2093C9.57206 14.5852 8.97055 14.6103 8.56955 14.2343L5.18609 11.1516C4.78509 10.7757 4.76003 10.1491 5.1109 9.74812C5.48684 9.34712 6.11341 9.32206 6.51441 9.698L9.19612 12.1541L14.9104 6.43985C15.3114 6.03885 15.938 6.03885 16.339 6.43985C16.74 6.84085 16.74 7.46742 16.339 7.86842Z"
                              fill="#7BB500"/>
                    </svg>
                </div>
            </div>
            <div class="discussion-item-content">
                <div class="discussion-item-head">
                    <a href="<?php echo $comment_link ?>"
                       class="link-js discussion-item__title">
						<?php echo ( $post_type == 'costs' ) ? 'Заявка на зміну робочого часу ' . $string : get_the_title( $project_id ) ?>
                    </a>
					<?php if ( $project_id )
						the_project_performers( $project_id ) ?>
                </div>
                <div class="discussion-item__text text">
					<?php echo $id ? replaceUrl( get_content_by_id( $id ) ) : ''; ?>
					<?php if ( $post_type == 'costs' && is_current_user_admin() ):
						$costs_confirmed = carbon_get_post_meta( $project_id, 'costs_confirmed' );
						if ( ! $costs_confirmed ):
							?>
                            <div class="row">
								<?php $sub_link = $link . '?id=' . $project_id . '&action=change_user_time'; ?>
                                <a href="<?php echo $sub_link; ?>" class="button default-link">Підтвердити зміну</a>
								<?php $sub_link = $link . '?id=' . $project_id . '&comment_id=' . $id . '&action=remove_change_user_time'; ?>
                                <a href="<?php echo $sub_link; ?>" class="button default-link">Відмінити зміну</a>
                            </div>
						<?php else: ?>
                            <p>
                                <strong>
                                    Підтвердженно <?php echo $costs_confirmed; ?>
                                </strong>
                            </p>
						<?php endif; ?>
					<?php endif; ?>
                </div>
            </div>
        </div>
	<?php
	endif;
}