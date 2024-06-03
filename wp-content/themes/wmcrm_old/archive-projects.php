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
$is_admin       = is_current_user_admin();
$route = $_GET['route'] ?? '';
if($route =='create' && $is_admin){
    the_create_project_page();
    die();
}
global $wp_query;
set_query_data();
the_projects_page();
get_header();
$worksection_id = carbon_get_user_meta( $user_id, 'worksection_id' );
$id             = get_the_ID();
$isLighthouse   = isLighthouse();
$size           = isLighthouse() ? 'thumbnail' : 'full';
$projects_url   = get_post_type_archive_link( 'projects' );
$s              = $_GET['search'] ?? '';
$performer      = $_GET['performer'] ?? ( $worksection_id ?: "" );
$orderby        = $_GET['orderby'] ?? '';
$order          = $_GET['desc'] ?? '';
$performers     = get_performers();
?>

<section class="section section-projects" id="list">
    <div class="container">
        <div class="section-projects-content">
            <div class="title ">
                Проєкти (<span class="found-posts"><?php echo $wp_query->found_posts; ?></span>)
            </div>
			<?php if ( $is_admin ): ?>
                <a href="<?php echo $projects_url . '?route=create' ?>" class="button button__create element-center link-js">
                    Створити проект
                </a>
			<?php endif; ?>
            <div class="projects-container">
                <form action="<?php echo $projects_url ?>" method="get" class="projects-head filter-project-form">
                    <div class="projects-head__item">
                        <input type="text" class="projects-head__input" name="search" placeholder="Назва проекту"
                               value="<?php echo $s ?>">
                    </div>
                    <div class="projects-head__item">
						<?php if ( $performers ): ?>
                            <select name="performer" class="selectric submit-on-select">
                                <option <?php echo $performer == '' ? 'selected' : ''; ?> value="">Виконавець</option>
								<?php foreach ( $performers as $performer_id => $performer_name ): if($performer_name): ?>
                                    <option <?php echo $performer == $performer_id ? 'selected' : ''; ?>
                                            value="<?php echo $performer_id ?>">
										<?php echo $performer_name ?>
                                    </option>
								<?php endif; endforeach; ?>
                            </select>
						<?php else: ?>
                            Виконавець
						<?php endif; ?>
                    </div>
                    <div class="projects-head__item">
                        <select name="orderby" class="selectric trigger-on-select submit-on-select">
                            <option disabled <?php echo $orderby == '' || $order == '' ? 'selected' : ''; ?>>
                                Дата
                            </option>
                            <option value="date" <?php echo $orderby == 'desc' && $order == 'desc' ? 'selected' : ''; ?>
                                    data-selector=".order-input" data-val="desc">
                                Новіші
                            </option>
                            <option value="date" <?php echo $orderby == 'desc' && $order == 'asc' ? 'selected' : ''; ?>
                                    data-selector=".order-input" data-val="asc">
                                Старіші
                            </option>
                        </select>

                    </div>
                    <input type="hidden" name="order" class="order-input" value="<?php echo $order; ?>">
                    <input type="hidden" name="type" value="next_project_page">
                </form>
                <div class="projects container-js" id="list-list">
					<?php
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							$id = get_the_ID();
							the_project();
						}
					} else {
						?>
                        <div class="title empty-title title-left">Не знайдено</div>
						<?php
					}
					?>
                </div>
            </div>
            <div class="pagination-wrapper pagination-js">
				<?php echo _get_next_link(); ?>
            </div>
        </div>
    </div>
</section>
<?php get_footer(); ?>
