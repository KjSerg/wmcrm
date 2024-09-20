<?php
function the_project( $id = false, $cls = '', $args = array() ) {
	$cockie          = $_COOKIE['selected_project'] ?? '';
	$string          = $_GET['string'] ?? '';
	$search_by       = $_GET['search_by'] ?? '';
	$link_to_comment = '';
	if ( $string && $search_by == 'comments' ) {
		$link_to_comment = get_the_permalink( $id ) . '?comments_count=-1&string=' . $string;
	}
	$current_user_admin = is_current_user_admin();
	$id                 = $id ?: get_the_ID();
	$parent_id          = wp_get_post_parent_id( $id );
	$permalink          = get_the_permalink( $id );
	$title              = get_the_title( $id );
	$post_status        = get_post_status( $id );
	$tags               = get_the_terms( $id, 'tags' );
	$tag_list           = $args['tags'] ?? array();
	$edit_cls           = $current_user_admin ? 'select-edit' : '';
	if ( $cockie ) {
		$cockie = explode( ',', $cockie );
		if ( in_array( $id, $cockie ) || in_array( - 1, $cockie ) ) {
			$edit_cls .= ' selected';
		}
	}
	?>
    <div class="project-item <?php echo $cls . ' ' . $edit_cls; ?>" id="project-<?php echo $id ?>"
         data-id="<?php echo $id; ?>">
        <div class="project-item-date">
			<?php if ( $current_user_admin ): ?>
                <div class="project-item-icon" style="background-image: url(<?php echo _i( 'check' ) ?>);"></div>
			<?php endif; ?>
			<?php
			echo get_the_date( 'd.m.Y H:i', $id );
			the_post_status_html( $post_status, $id );
			?>
        </div>
        <div class="project-item-name">
            <a href="<?php echo $link_to_comment ?: get_the_permalink( $id ) ?>"
               class="project-item-title  <?php echo !$link_to_comment ?'  link-js open-in-modal': '' ?>">
				<?php echo get_the_title( $id ); ?>
            </a>
			<?php the_project_tags_html( $tags, $tag_list ); ?>
        </div>
		<?php the_project_performers( $id ) ?>
		<?php if ( $current_user_admin ): ?>
            <a href="#deleting-window-<?php echo $id ?>" class="project-item__remove modal-open">
				<?php _s( _i( 'remove' ) ) ?>
            </a>
            <div class="dialog-window deleting-window" id="deleting-window-<?php echo $id ?>">
                <div class="dialog-title">Видалити "<?php echo get_the_title( $id ); ?>" ?</div>
                <div class="deleting-window-controls">
                    <a href="#" data-id="<?php echo $id ?>" class="button deleting-project">
                        Видалити
                    </a>
                    <a href="#" class="button button--bordered window-close">
                        Закрити вікно
                    </a>
                </div>
            </div>
		<?php endif; ?>
    </div>
	<?php
	if ( is_empty_query() ) {
		$children = get_children( array( 'post_parent' => $id ) );
		$cls      = $cls == 'child' ? $cls . ' sub-child' : 'child';
		if ( $children ) {
			foreach ( $children as $child ) {
				the_project( $child->ID, $cls );
			}
		}
	}

}

function the_project_performers( $id ) {
	if ( get_post_type( $id ) == 'costs' ) {
		$author_id = get_post_author_id( $id );
		$user      = get_user_by( 'id', $author_id );
		$name      = $user->display_name;
		$avatar    = carbon_get_user_meta( $author_id, 'avatar' );
		$avatar    = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $author_id );
		?>
        <div class="project-item-performers">
            <div class="project-item-performer ">
				<?php echo $name; ?>
				<?php if ( $avatar ): ?>
                    <span class="project-item-performer__avatar"><img src="<?php echo $avatar; ?>"
                                                                      alt=""></span>
				<?php endif; ?>
            </div>
        </div>
		<?php
	} else {
		$projects            = get_post_type_archive_link( 'projects' );
		$performer_id        = carbon_get_post_meta( $id, 'worksection_user_to_id' );
		$performer_name      = carbon_get_post_meta( $id, 'worksection_user_to_name' );
		$project_users_to_id = carbon_get_post_meta( $id, 'project_users_to_id' );
		if ( $project_users_to_id ): $project_users_to_id = explode( ',', $project_users_to_id );
			$number          = 1;
			?>
            <div class="project-item-performers">
				<?php foreach ( $project_users_to_id as $_user_id ):
					if ( $u = get_user_by( 'id', $_user_id ) ):
						if ( $number < 3 ):
							$url = get_author_posts_url( $_user_id );
							$avatar       = carbon_get_user_meta( $_user_id, 'avatar' );
							$avatar       = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $_user_id );
							$last_name    = $u->last_name;
							$first_name   = $u->first_name;
							$display_name = $last_name;
							if ( $first_name ) {
								$display_name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
							}
							?>
                            <a href="<?php echo $url ?>" class="project-item-performer link-js">
								<?php echo $display_name; ?>
                                <span class="project-item-performer__avatar"><img src="<?php echo $avatar; ?>"
                                                                                  alt=""></span>
                            </a>
						<?php endif;
						$number           = $number + 1;
					endif;
				endforeach;
				if ( count( $project_users_to_id ) > 2 ):
					?>
                    <span class="project-item-performers__more">...</span>
				<?php
				endif;
				?>
            </div>
		<?php else: ?>
			<?php if ( $performer_id ):
				$user = get_user_by_work_section_id( $performer_id );
				$avatar      = false;
				$_url        = $projects . '?performer=' . $performer_id;
				if ( $user ) {
					$_user_id       = $user->ID;
					$avatar         = carbon_get_user_meta( $_user_id, 'avatar' );
					$avatar         = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $_user_id );
					$last_name      = $user->last_name;
					$first_name     = $user->first_name;
					$performer_name = $last_name;
					$_url           = get_author_posts_url( $_user_id );
					if ( $first_name ) {
						$performer_name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
					}
				}
				?>
                <div class="project-item-performers">
                    <a href="<?php echo $_url ?>"
                       class="project-item-performer link-js">
						<?php echo $performer_name; ?>
						<?php if ( $avatar ): ?>
                            <span class="project-item-performer__avatar"><img src="<?php echo $avatar; ?>"
                                                                              alt=""></span>
						<?php endif; ?>
                    </a>
                </div>
			<?php else: ?>
                <div class="project-item-performers"></div>
			<?php endif; ?>
		<?php endif;
	}
}

function the_performers( $id ) {
	$projects            = get_post_type_archive_link( 'projects' );
	$performer_id        = carbon_get_post_meta( $id, 'worksection_user_to_id' );
	$performer_name      = carbon_get_post_meta( $id, 'worksection_user_to_name' );
	$project_users_to_id = carbon_get_post_meta( $id, 'project_users_to_id' );
	if ( $project_users_to_id ):
		$project_users_to_id = explode( ',', $project_users_to_id );
		foreach ( $project_users_to_id as $_user_id ):
			the__user( $_user_id, 'Відповідальний' );
		endforeach;
	else:
		if ( $performer_id ):
			$user = get_user_by_work_section_id( $performer_id );
			$avatar      = false;
			if ( $user ) {
				$_user_id       = $user->ID;
				$avatar         = carbon_get_user_meta( $_user_id, 'avatar' );
				$avatar         = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $_user_id );
				$last_name      = $user->last_name;
				$first_name     = $user->first_name;
				$performer_name = $last_name;
				if ( $first_name ) {
					$performer_name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
				}
			}
			?>
            <div class="project-user">
				<?php if ( $avatar ): ?>
                    <div class="project-user__avatar">
                        <img src="<?php echo $avatar; ?>" alt="">
                    </div>
				<?php endif; ?>
                <div class="project-user-text">
                    <div class="project-user__title"><?php echo $performer_name; ?></div>
                    <div class="project-user__role">Відповідальний</div>
                </div>
            </div>
		<?php
		endif;
	endif;
}

function the_project_tags_html( $tags, $tag_list ) {
	$subtype = $_GET['subtype'] ?? '';
	if ( is_current_user_admin() ) {
		if ( $tag_list ) {
			$tag_color = $tags ? carbon_get_term_meta( $tags[0]->term_id, 'tag_color' ) : '';
			?>
            <form class="form-js project-tag-form" method="post"
                  style="background-color: <?php echo $tag_color ?: '#7BB500'; ?>;"
                  id="project-tag-form-<?php echo get_the_ID() . '-' . $subtype; ?>">
                <input type="hidden" name="action" value="change_project_tag">
                <input type="hidden" name="id" value="<?php echo get_the_ID() ?>">
                <select name="project_tag" class="selectric  submit-on-select">
                    <option selected value="">
                        Теги
                    </option>
					<?php foreach ( $tag_list as $_tag ):
						$color = '#7BB500';
						$tag_color = carbon_get_term_meta( $_tag->term_id, 'tag_color' );
						$color = $tag_color ?: $color;
						?>
                        <option value="<?php echo $_tag->term_id ?>"
                                data-color="<?php echo $color; ?>"
							<?php echo is_current_tag( $_tag->term_id, $tags ) ? 'selected' : ''; ?> >
							<?php echo $_tag->name; ?>
                        </option>
					<?php endforeach; ?>
                </select>
            </form>
			<?php
		}
	} else {
		if ( $tags ) {
			echo '<div class="project-item-tags">';
			foreach ( $tags as $tag ) {
				$color     = '#7BB500';
				$tag_color = carbon_get_term_meta( $tag->term_id, 'tag_color' );
				$color     = $tag_color ?: $color;
				?>
                <div
                        style="background-color: <?php echo $color; ?>;"
                        class="project-item-tag">
					<?php echo $tag->name; ?>
                </div>
				<?php
			}
			echo '</div>';
		}
	}
}

function the_post_status_html( $post_status, $id ) {
	$statuses = array(
		'publish' => 'В роботі',
		'archive' => 'Завершена',
		'pending' => 'В черзі'
	);
	$is_admin = is_current_user_admin();
	$cls      = ! $is_admin ? ' not-active' : '';
	if ( $statuses ):
		echo ' <div class="project-item-statuses">';
		foreach ( $statuses as $key => $status ):
			if ( $key === $post_status ):
				$color = '#7BB500';
				if ( $key == 'pending' ) {
					$color = '#333';
				} elseif ( $key == 'archive' ) {
					$color = '#9B9EBE';
				}
				?>
                <span class="project-item-status active <?php echo $cls; ?>" data-id="<?php echo $id; ?>"
                      data-status="<?php echo $key; ?>"
                      style="background-color:<?php echo $color; ?>"><?php echo $status; ?></span>
			<?php
			endif;
		endforeach;
		foreach ( $statuses as $key => $status ):
			$color = '#7BB500';
			if ( $key == 'pending' ) {
				$color = '#333';
			} elseif ( $key == 'archive' ) {
				$color = '#9B9EBE';
			}
			?>
            <span class="project-item-status <?php echo $cls; ?>" data-id="<?php echo $id; ?>"
                  data-status="<?php echo $key; ?>"
                  style="background-color:<?php echo $color; ?>"><?php echo $status; ?></span>
		<?php
		endforeach;
		echo '</div>';
	endif; ?>


	<?php
}

function the_projects_page() {
	$type    = $_GET['type'] ?? '';
	$arr     = array();
	$tags    = get_terms( array(
		'taxonomy'   => 'tags',
		'hide_empty' => false,
	) );
	$user_id = get_current_user_id();
	if ( $_GET && $user_id ) {
		foreach ( $_GET as $key => $val ) {
			if ( $key != 'type' && $key != 's' && $val != '' && $key != 'string' && $key != 'search_by' ) {
				$arr[ $key ] = $val;
			}
		}
		if ( $arr ) {
			carbon_set_user_meta( $user_id, 'user_project_filter', json_encode( $arr ) );
		} else {
			carbon_set_user_meta( $user_id, 'user_project_filter', '' );
		}
	}
	if ( $type == 'next_project_page' ) {
		global $wp_query;
		?>
        <span class="found-posts"><?php echo $wp_query->found_posts; ?></span>
        <div class="projects container-js" id="list-list">
			<?php
			set_sub_query_data();
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					$id = get_the_ID();
					the_project( $id, '', array(
						'tags' => $tags
					) );
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

function the_observers( $id ) {
	$observers_id = carbon_get_post_meta( $id, 'project_users_observer_id' );
	if ( $observers_id ) {
		$observers_id = explode( ',', $observers_id );
		foreach ( $observers_id as $_user_id ):
			the__user( $_user_id, 'Спостерігач' );
		endforeach;
	}
}

function the__user( $_user_id, $role = '' ) {
	global $post;
	if ( $u = get_user_by( 'id', $_user_id ) ):
		$worksection_id = carbon_get_user_meta( $_user_id, 'worksection_id' );
		$avatar = carbon_get_user_meta( $_user_id, 'avatar' );
		$avatar = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $_user_id );
		$last_name = $u->last_name;
		$first_name = $u->first_name;
		$display_name = $last_name;
		if ( $first_name ) {
			$display_name .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
		}
		$title = $display_name;
		if ( $role ) {
			$title .= ' - ' . $role;
		}
		$role_hush = md5( $role );
		$users     = get_active_users();
		?>
        <div class="project-user" title="<?php echo $title; ?>">
            <a href="#edit-user-<?php echo $role_hush; ?>" class="project-user__avatar modal-open">
                <img src="<?php echo $avatar; ?>" alt="">
            </a>
            <div class="project-user-text">
                <a class="project-user__title modal-open"
                   href="#edit-user-<?php echo $role_hush; ?>"><?php echo $display_name; ?></a>
				<?php if ( $role ): ?>
                    <div class="project-user__role"><?php echo $role; ?></div>
				<?php endif; ?>
            </div>
        </div>
		<?php if ( $role != 'Автор' ):
		$_users = [];
		if ( $role == 'Відповідальний' ) {
			$project_users_to_id = carbon_get_post_meta( $post->ID, 'project_users_to_id' );
			$_users              = $project_users_to_id ? explode( ',', $project_users_to_id ) : array();
		}
		if ( $role == 'Спостерігач' ) {
			$project_users_observer_id = carbon_get_post_meta( $post->ID, 'project_users_observer_id' );
			$_users                    = $project_users_observer_id ? explode( ',', $project_users_observer_id ) : array();
		}
		if ( is_current_user_admin() ):
			?>
            <div class=" modal-window-change-user modal-window" id="edit-user-<?php echo $role_hush; ?>">
                <div class="title">
                    Змінити користувачів із ролю <br> <?php echo $role; ?>
                </div>
                <form class="form form-js change-project-users-form" id="change-project-users-form" method="post">
                    <input type="hidden" name="action" value="change_project_user">
                    <input type="hidden" name="role" value="<?php echo $role; ?>">
                    <input type="hidden" name="project_id" value="<?php echo $post->ID; ?>">
					<?php if ( $users ): ?>
                        <label class="form-group ">
                            <span class="form-group__title"><?php echo $role; ?></span>
                            <select name="<?php echo $role; ?>[]" multiple class="selectric">
                                <option disabled><?php echo $role; ?></option>
								<?php foreach ( $users as $user ):
									$attr = in_array( $user->ID, $_users ) ? 'selected' : '';
									?>
                                    <option value="<?php echo esc_attr( $user->ID ) ?>" <?php echo esc_attr( $attr ) ?>>
										<?php echo esc_html( $user->display_name ) ?>
                                    </option>
								<?php endforeach; ?>
                            </select>
                        </label>
					<?php endif; ?>
                    <div class="form-buttons">
                        <button class="form-button button">
                            Змінити <?php echo $role ?>
                        </button>
                    </div>
                </form>
            </div>
		<?php endif; ?>
	<?php endif; ?>
	<?php
	endif;
}

function the_project_author( $id ) {
	$author_id = get_post_field( 'post_author', $id );
	the__user( $author_id, 'Автор' );
}