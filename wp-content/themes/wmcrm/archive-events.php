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
get_header();
$id           = get_the_ID();
$isLighthouse = isLighthouse();
$size         = isLighthouse() ? 'thumbnail' : 'full';
?>
    <section class="section events-section">
        <div class="container">
            <div class="events-list container-js" id="events-list">
				<?php
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						$id = get_the_ID();
						the_event($id, true);
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
    </section>
<?php
get_footer(); ?>