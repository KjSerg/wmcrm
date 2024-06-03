<?php
function the_project( $id = false, $cls = '' ) {
	$cockie             = $_COOKIE['selected_project'] ?? '';
	$current_user_admin = is_current_user_admin();
	$id                 = $id ?: get_the_ID();
	$parent_id          = wp_get_post_parent_id( $id );
	$permalink          = get_the_permalink( $id );
	$title              = get_the_title( $id );
	$post_status        = get_post_status( $id );
	$tags               = get_the_terms( $id, 'tags' );
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
			the_post_status_html( $post_status );
			?>
        </div>
        <div class="project-item-name">
            <a href="<?php echo get_the_permalink( $id ) ?>" class="project-item-title link-js open-in-modal">
				<?php echo get_the_title( $id ); ?>
            </a>
			<?php the_project_tags_html( $tags ); ?>
        </div>
		<?php the_project_performers( $id ) ?>
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
                $_url = $projects . '?performer=' . $performer_id;
				if ( $user ) {
					$_user_id       = $user->ID;
					$avatar         = carbon_get_user_meta( $_user_id, 'avatar' );
					$avatar         = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $_user_id );
					$last_name      = $user->last_name;
					$first_name     = $user->first_name;
					$performer_name = $last_name;
					$_url = get_author_posts_url($_user_id);
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

function the_project_tags_html( $tags ) {
	if ( $tags ) {
		echo '<div class="project-item-tags">';
		foreach ( $tags as $tag ) {
			$color     = '#7BB500';
			$tag_color = carbon_get_term_meta( $tag->term_id, 'tag_color' );
			$color     = $tag_color ?: $color;
			?>
            <a href="<?php echo get_term_link( $tag->term_id ) ?>"
               style="background-color: <?php echo $color; ?>;"
               class="project-item-tag">
				<?php echo $tag->name; ?>
            </a>
			<?php
		}
		echo '</div>';
	}
}

function the_post_status_html( $post_status ) {
	$color  = '#7BB500';
	$string = 'В роботі';
	if ( $post_status == 'pending' ) {
		$color  = '#333';
		$string = 'В черзі';
	} elseif ( $post_status == 'archive' ) {
		$color  = '#9B9EBE';
		$string = 'Завершена';
	}
	echo '<span class="project-item-status" style="background-color: ' . $color . ';">' . $string . '</span>';
}

function the_projects_page() {
	$type    = $_GET['type'] ?? '';
	$arr     = array();
	$user_id = get_current_user_id();
	if ( $_GET && $user_id ) {
		foreach ( $_GET as $key => $val ) {
			if ( $key != 'type' && $key != 's' && $val != '' ) {
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
		?>
        <div class="project-user" title="<?php echo $title; ?>">
            <div class="project-user__avatar">
                <img src="<?php echo $avatar; ?>" alt="">
            </div>
            <div class="project-user-text">
                <div class="project-user__title"><?php echo $display_name; ?></div>
				<?php if ( $role ): ?>
                    <div class="project-user__role"><?php echo $role; ?></div>
				<?php endif; ?>
            </div>
        </div>
	<?php
	endif;
}

function the_project_author( $id ) {
	$author_id = get_post_field( 'post_author', $id );
	the__user( $author_id, 'Автор' );
}