<?php

namespace wmcrm\components;

use WP_Query;

class Board {

	public function render(): void {
		$this->render_head();
		$this->render_board();
	}

	public function render_head() {
		?>
        <div class="board-wrapper-head">
            <div class="board-wrapper-head__item">В черзі</div>
            <div class="board-wrapper-head__item">В роботі</div>
            <div class="board-wrapper-head__item">Завершено</div>
        </div>
		<?php
	}

	public function render_board(): void {
		?>
        <div class="board">
            <div class="board-column">
				<?php $this->render_list( 'pending' ); ?>
            </div>
            <div class="board-column"><?php $this->render_list( 'publish' ); ?></div>
            <div class="board-column"><?php $this->render_list( 'archive' ); ?></div>
        </div>
		<?php
	}

	private function get_query_args( string $status ): array {
		$args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'projects',
			'post_status'    => 'pending',
		);
        return $args;
	}

	private function render_list( string $status ): void {
		$query = new WP_Query( $this->get_query_args( $status ) );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$id    = get_the_ID();
				$title = get_the_title();
				?>
                <div id="<?php echo $id ?>" class="board-item <?php echo $status ?>" data-status="<?php echo $status ?>">
					<?php echo $title ?>
                </div>
				<?php
			}
		}
		wp_reset_postdata();
		wp_reset_query();
	}
}