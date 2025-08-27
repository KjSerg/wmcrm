<?php

namespace WMCRM\core;

use WP_Error;
use WP_Query;

class Comments {
	public static function render( $id ) {
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
						Comment::the_comment_project( $comment_id, false, $id );
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
}

class Comment {
	public static function the_project_comment(): void {
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
						<?php echo $id ? replaceUrl( get_the_excerpt( $id ) ) : ''; ?>
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

	public static function the_comment_project( $comment_id, $user_id = false, $project_id = false ): void {
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
		$likes                 = get_post_meta( $comment_id, '_likes', true );
		$likes                 = $likes ? json_decode( $likes, true ) : [];
		$like_count            = count( $likes );
		$is_liked              = in_array( $user_id, $likes );
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
						echo "<div class='comment-author__avatar'><img class='cover' loading='lazy' src='$avatar' alt=''/></div>";
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
                        <a href="#" data-id="<?php echo $comment_id ?>"
                           class="comment-change change-btn comment-change-js">
							<?php _s( _i( 'edit' ) ) ?>
                        </a>


					<?php endif; ?>
                    <div class="comment-like-wrapper" data-id="<?php echo $comment_id ?>">
                        <a href="<?php echo site_url( 'wp-admin/admin-ajax.php?action=comment_like&id=' . $comment_id ); ?>"
                           class="comment-like comment-like-js <?php echo $is_liked ? 'active' : '' ?>">
                        <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg"> <path
                                    d="M28.706 20.3952C29.3208 19.611 29.6157 18.7704 29.578 17.9046C29.5404 16.9511 29.1138 16.2045 28.7625 15.7465C29.1703 14.7302 29.3271 13.1305 27.9658 11.8883C26.9683 10.9787 25.2744 10.5709 22.9281 10.6838C21.2782 10.7591 19.898 11.0665 19.8415 11.079H19.8353C19.5216 11.1355 19.1891 11.2045 18.8503 11.2798C18.8252 10.8783 18.8942 9.8808 19.6345 7.63488C20.5128 4.96236 20.4626 2.91719 19.4714 1.54956C18.43 0.112924 16.7675 0 16.2782 0C15.8077 0 15.3748 0.194479 15.0674 0.55207C14.371 1.36135 14.4526 2.85445 14.5404 3.54454C13.7123 5.76537 11.3911 11.2108 9.42748 12.7227C9.38984 12.7478 9.35847 12.7792 9.32711 12.8105C8.74994 13.4191 8.36098 14.0778 8.0975 14.655C7.72736 14.4542 7.30703 14.3413 6.85534 14.3413H3.02849C1.58558 14.3413 0.418701 15.5144 0.418701 16.9511V27.1455C0.418701 28.5885 1.59185 29.7553 3.02849 29.7553H6.85534C7.41368 29.7553 7.93438 29.5797 8.36098 29.2785L9.83526 29.4542C10.0611 29.4856 14.0762 29.9937 18.1979 29.9122C18.9444 29.9686 19.6471 30 20.2995 30C21.4225 30 22.4011 29.9122 23.2167 29.7365C25.1364 29.3287 26.4476 28.5132 27.1126 27.3149C27.6207 26.399 27.6207 25.4893 27.5392 24.9122C28.7876 23.7829 29.0072 22.5345 28.9632 21.6562C28.9381 21.1481 28.8252 20.7152 28.706 20.3952ZM3.02849 28.0615C2.52033 28.0615 2.11255 27.6474 2.11255 27.1455V16.9448C2.11255 16.4366 2.52661 16.0289 3.02849 16.0289H6.85534C7.36349 16.0289 7.77127 16.4429 7.77127 16.9448V27.1393C7.77127 27.6474 7.35722 28.0552 6.85534 28.0552H3.02849V28.0615ZM27.1 19.6612C26.8365 19.9373 26.7863 20.3576 26.9871 20.6838C26.9871 20.6901 27.2443 21.1292 27.2757 21.7315C27.3196 22.5533 26.9243 23.2811 26.0962 23.9021C25.8014 24.128 25.6822 24.5169 25.8077 24.8683C25.8077 24.8745 26.0774 25.7026 25.6383 26.4868C25.2179 27.2396 24.2832 27.7792 22.8654 28.0803C21.7299 28.325 20.1866 28.3689 18.292 28.2183C18.2669 28.2183 18.2355 28.2183 18.2041 28.2183C14.1703 28.3061 10.0925 27.7792 10.0486 27.7729H10.0423L9.40866 27.6976C9.4463 27.522 9.46512 27.3337 9.46512 27.1455V16.9448C9.46512 16.675 9.42121 16.4115 9.34593 16.1669C9.45885 15.7465 9.77253 14.8118 10.5128 14.0151C13.3296 11.7817 16.0837 4.24718 16.2029 3.92095C16.2531 3.78921 16.2656 3.64492 16.2405 3.50063C16.1339 2.79799 16.1715 1.93852 16.3221 1.6813C16.6546 1.68758 17.5517 1.78168 18.0912 2.52823C18.7311 3.4128 18.706 4.99373 18.0159 7.08908C16.962 10.2823 16.8742 11.9636 17.7085 12.7039C18.1226 13.074 18.6747 13.0928 19.0762 12.9486C19.4589 12.8607 19.8227 12.7854 20.1678 12.729C20.1929 12.7227 20.2242 12.7164 20.2493 12.7102C22.1753 12.2898 25.6257 12.0326 26.824 13.1242C27.8403 14.0527 27.1188 15.2823 27.0373 15.4141C26.8051 15.7654 26.8742 16.2233 27.1878 16.5056C27.1941 16.5119 27.8528 17.133 27.8842 17.9674C27.9093 18.5257 27.6458 19.0966 27.1 19.6612Z"></path> </svg>
                        </a>
                        (
                        <span class="comment-likes-counter"><?php echo $like_count ?></span>
                        )
                        <div class="comment-like-wrapper-list"></div>
                    </div>
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

	public static function set_comment_to_users( $comment_id, $users_id ): array {
		$arr = [];
		if ( ! $users_id ) {
			return $arr;
		}
		$comment_id = intval( $comment_id );
		if ( ! get_post( $comment_id ) ) {
			return $arr;
		}
		if ( get_post_type( $comment_id ) !== 'discussion' ) {
			return $arr;
		}
		$users_id = array_map( 'intval', $users_id );
		foreach ( $users_id as $user_id ) {
			if ( ! $user = get_user_by( 'id', $user_id ) ) {
				continue;
			}
			if ( ! $r = self::add_user_to_discussion_as_term( $user_id, $comment_id ) ) {
				continue;
			}
			$arr[] = $user_id;
		}

		return $arr;
	}

	public static function set_comments_to_user( $comments_id, $user_id ): array {
		$user_id = intval( $user_id );
		$arr     = [];
		if ( ! $user = get_user_by( 'id', $user_id ) ) {
			return $arr;
		}
		if ( ! $comments_id ) {
			return $arr;
		}
		$comments_id = array_map( 'intval', $comments_id );
		foreach ( $comments_id as $id ) {
			if ( ! self::add_user_to_discussion_as_term( $user_id, $id ) ) {
				continue;
			}
			$arr[] = $id;
		}

		return $arr;
	}

	public static function add_user_to_discussion_as_term( $user_id, $post_id ): WP_Error|bool|array {
		if ( ! is_numeric( $user_id ) || ! is_numeric( $post_id ) ) {
			return new WP_Error( 'invalid_data', 'ID користувача та поста повинні бути числами.' );
		}
		if ( 'discussion' !== get_post_type( $post_id ) ) {
			return new WP_Error( 'invalid_post_type', 'Пост не є типом "discussion".' );
		}
		$taxonomy = 'involved_users';
		$term     = (string) $user_id;
		$result   = wp_set_post_terms( $post_id, $term, $taxonomy, true );
		if ( is_wp_error( $result ) ) {
			error_log( 'Помилка при додаванні терму involved_users: ' . $result->get_error_message() );

			return $result;
		}

		return true;
	}

	public static function set_comment_term_to_users( $project_id, $comment_id ) {
		$arr        = [];
		$project_id = intval( $project_id );
		$comment_id = intval( $comment_id );
		if ( ! get_post( $comment_id ) ) {
			return $arr;
		}
		if ( ! get_post( $project_id ) ) {
			return $arr;
		}
		$users                     = [];
		$project_user_from_id      = carbon_get_post_meta( $project_id, 'project_user_from_id' );
		$project_users_to_id       = carbon_get_post_meta( $project_id, 'project_users_to_id' );
		$project_users_observer_id = carbon_get_post_meta( $project_id, 'project_users_observer_id' );
		$users[]                   = $project_user_from_id;
		if ( $project_users_to_id ) {
			$project_users_to_id = explode( ',', $project_users_to_id );
			$users               = array_merge( $users, $project_users_to_id );
		}
		if ( $project_users_observer_id ) {
			$project_users_observer_id = explode( ',', $project_users_observer_id );
			$users                     = array_merge( $users, $project_users_observer_id );
		}
		$users = array_map( 'intval', $users );
		$users = array_unique( $users );
		if ( count( $users ) === 0 ) {
			return $arr;
		}

		return self::set_comment_to_users( $comment_id, $users );
	}
}