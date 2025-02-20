<?php

namespace wmcrm\components;

use WP_Query;

class Board {

	/**
	 * @var array|string[]
	 */
	private array $statuses;
	/**
	 * @var int[]|string[]
	 */
	private array $data;

	public function render( $statuses = [] ): void {
		$this->statuses = $statuses ?: [ 'pending' => 'В черзі', 'publish' => 'В роботі', 'archive' => 'Завершено' ];
		$this->data     = [];
		$this->set_data();
		$this->render_head();
		$this->render_board();
		$this->render_footer();
	}




	public function render_head(): void {
		?>
        <div class="board-wrapper-head">
			<?php foreach ( $this->statuses as $status_name => $status ): ?>
                <div class="board-wrapper-head__item">
					<?php echo $status; ?>
                    <form action="<?php echo get_bloginfo( 'url' ); ?>" method="get"
                          class="board-wrapper-head-form">
                        <input type="hidden" name="route" value="board">
                        <input type="hidden" name="post_status" value="<?php echo $status_name; ?>">
                        <input type="text" name="search_<?php echo $status_name; ?>" value="">
                    </form>
                </div>
			<?php endforeach; ?>
        </div>
		<?php
	}

	public function render_board(): void {
		?>
        <div class="board">
			<?php foreach ( $this->statuses as $status => $status_title ): ?>
                <div class="board-column" data-status="<?php echo $status; ?>">
					<?php $this->render_list( $status ); ?>
                </div>
			<?php endforeach; ?>
        </div>
		<?php
	}

	private function get_query_args( string $status ): array {
		$default_posts_per_page = get_option( 'posts_per_page' ) ?: 10;
		$s                      = filter_input( INPUT_GET, 'search_' . $status );
		$args                   = array(
			'posts_per_page' => $default_posts_per_page,
			'post_type'      => 'projects',
			'orderby'        => 'modified',
			'order'          => 'desc',
			'post_status'    => $status,
			'paged'          => (int) ( filter_input( INPUT_GET, 'page_number_' . $status, FILTER_SANITIZE_NUMBER_INT ) ?: 1 ),
		);
		if ( $s ) {
			$args['s']     = $s;
		}

		return $args;
	}

	private function render_project( $id, $status ): void {
		$title = get_the_title();
		?>
        <div id="<?php echo $id ?>" class="board-item no-select <?php echo $status ?>"
             data-status="<?php echo $status ?>">
			<?php echo $title; ?>
        </div>
		<?php
	}

	private function render_list( string $status ): void {
		$query                                  = new WP_Query( $this->get_query_args( $status ) );
		$max_pages                              = $query->max_num_pages;
		$this->data[ $status ]['max_num_pages'] = $max_pages;
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$id = get_the_ID();
				$this->render_project( $id, $status );
			}
		}
		wp_reset_postdata();
		wp_reset_query();
	}

	public function get_next_button_url( $status ): string {
		$url                  = get_bloginfo( 'url' ) . '/';
		$max_pages            = $this->data[ $status ]['max_num_pages'] ?? 1;
		$current_page         = $this->data[ $status ]['current_page'] ?? 1;
		$next_page            = min( $current_page + 1, $max_pages );
		$p_k                  = 'page_number_' . $status;
		$query_params         = $_GET;
		$query_params[ $p_k ] = $next_page;
		$query_string         = http_build_query( $query_params );
        if($current_page >= $max_pages) {
            return '';
        }
		return $next_page > 0 ? $url . '?' . $query_string : '';
	}

	private function render_footer(): void {

		?>
        <div class="board-wrapper-footer">
			<?php foreach ( $this->data as $status_name => $data ): ?>
                <div class="board-wrapper-footer__item" data-status="<?php echo $status_name ?>">
					<?php if ( $data['max_num_pages'] > 1 ):
						if ( $link = $this->get_next_button_url( $status_name ) ):
							?>
                            <a href="<?php echo $link ?>"
                               data-status="<?php echo $status_name ?>"
                               class="get-next-board-projects">Підгрузити</a>
						<?php endif; ?>
					<?php endif; ?>
                </div>
			<?php endforeach; ?>
        </div>
		<?php
	}

	private function set_data(): void {
		$new_data = [];
		foreach ( $this->statuses as $status_name => $status ) {
			$new_data[ $status_name ] = [
				'current_page'  => (int) ( filter_input( INPUT_GET, 'page_number_' . $status_name, FILTER_SANITIZE_NUMBER_INT ) ?: 1 ),
				'max_num_pages' => 1,
			];
		}
		$this->data = $new_data;
	}
}