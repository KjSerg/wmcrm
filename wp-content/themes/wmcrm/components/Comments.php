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
		$paged                   = filter_input( INPUT_GET, 'pagenumber', FILTER_SANITIZE_NUMBER_INT ) ?: 1;
		$post__in                = [];
		$meta_query              = [
			[
				'key'   => '_discussion_project_id',
				'value' => $id,
			]
		];
		$query_args              = array(
			'post_type'      => array( 'comments', 'discussion' ),
			'post_status'    => 'publish',
			'posts_per_page' => $default_posts_per_page,
			'paged'          => $paged,
			'fields'         => 'ids',
		);
		$test                    = $comment_ids || $worksection_comment_ids;
		if ( $test ) {
			$worksection_comment_ids = explode( ',', $worksection_comment_ids );
			$comment_ids             = explode( ',', $comment_ids );
			$comments_collection     = array_merge( $worksection_comment_ids, $comment_ids );
			$comments_collection     = array_map( 'intval', (array) $comments_collection );
			$comments_collection     = array_filter( $comments_collection, 'is_numeric' );
			$comments_collection     = array_filter( $comments_collection, fn( $item ) => $item > 0 );
			if ( $comments_collection ) {
				$post__in = $comments_collection;
			}
		}
		if ( empty( $post__in ) ) {
			$query_args['meta_query'] = $meta_query;
		} else {
			if ( count( $post__in ) > 200 ) {
				$query_args['meta_query'] = $meta_query;
			} else {
				$query_args['post__in'] = $post__in;
			}
		}
		if ( $comments_count ) {
			$query_args['posts_per_page'] = (int) $comments_count;
		}
		$query = new WP_Query( $query_args );
		if ( $query->have_posts() ) {
			?>
			<div class="section-comments-list container-js"><?php
				foreach ( $query->posts as $comment_id ) {
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

	}
}