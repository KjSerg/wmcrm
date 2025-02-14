<?php
$user_id  = get_current_user_id();
$var      = variables();
$set      = $var['setting_home'];
$assets   = $var['assets'];
$url      = $var['url'];
$url_home = $var['url_home'];
if ( ! $user_id ) {
	header( 'Location: ' . $url );
	die();
}
change_user_time_event();
get_header();
$id           = get_the_ID();
$isLighthouse = isLighthouse();
$size         = isLighthouse() ? 'thumbnail' : 'full';
the_events_section();
set_discussion_query_data();
?>
    <section class="section discussion-section">
        <div class="container">
            <div class="discussion-container">
                <div class="discussion-head">
                    <div class="title">
                        Коментарі до проектів
                    </div>
                </div>
                <div class="discussion-section-content">
                    <div class="projects-head discussion-table-head">
                        <div class="projects-head__column projects-head__date">
                            Дата
                        </div>
                        <div class="projects-head__column projects-head__title">
                            Назва задачі
                        </div>
                        <div class="projects-head__column projects-head__performer">
                            Виконавець
                        </div>
                    </div>
                    <div class="discussion container-js" id="discussion-list">
						<?php
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								$id = get_the_ID();
								the_project_comment();
							}
						} else {
							?>
                            <div class="title empty-title title-left">Не знайдено</div>
							<?php
						}
						?>
                    </div>
                    <div class="pagination-wrapper pagination-js">
						<?php echo _get_next_link(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
get_footer(); ?>