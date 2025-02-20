<?php
function the_board(): void {
	$board = new \wmcrm\components\Board();
	get_header();
	?>
    <section class="section section-projects" id="board">
        <div class="container">
            <div class="section-projects-head">
                <div class="title">
                    Проєкти
                </div>
            </div>
            <div class="board-wrapper">
                <div class="board-container"><?php $board->render(); ?></div>

            </div>
        </div>
    </section>
	<?php
	get_footer();
}