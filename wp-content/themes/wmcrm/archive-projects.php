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
user_filter_redirect();
$is_admin = is_current_user_admin();
$route    = filter_input( INPUT_GET, 'route' ) ?: '';
if ( $route == 'create' ) {
	the_create_project_page();
	die();
}

get_header();
global $wp_query;
set_query_data();
the_projects_page();
$worksection_id = carbon_get_user_meta( $user_id, 'worksection_id' );
$id             = get_the_ID();
$isLighthouse   = isLighthouse();
$size           = isLighthouse() ? 'thumbnail' : 'full';
$projects_url   = get_post_type_archive_link( 'projects' );
$s              = $_GET['s'] ?? '';
$performer      = $_GET['performer'] ?? ( $worksection_id ?: "" );
$_user_id       = $_GET['user_id'] ?? '';
$orderby        = $_GET['orderby'] ?? '';
$order          = $_GET['order'] ?? '';
$tag_get        = $_GET['project-tag'] ?? '';
$status_get     = $_GET['project-status'] ?? '';
$color_get      = $_GET['color'] ?? '';
$cockie         = $_COOKIE['selected_project'] ?? '';
if ( $cockie ) {
	$cockie = explode( ',', $cockie );
} else {
	$cockie = [];
}
$users              = get_active_users();
$tags               = get_terms( array(
	'taxonomy'   => 'tags',
	'hide_empty' => false,
) );
$colors             = get_terms( array(
	'taxonomy'   => 'colors',
	'hide_empty' => false,
) );
$statuses           = array(
	'publish' => 'В роботі',
	'archive' => 'Завершена',
	'pending' => 'В черзі'
);
$current_user_admin = is_current_user_admin();
?>

<section class="section section-projects" id="list">
    <div class="container">
        <div class="section-projects-head">
            <div class="title">
                Проєкти (
                <span class="found-posts"><?php echo $wp_query->found_posts; ?></span>
                )
            </div>
            <form action="<?php echo $projects_url ?>" method="get" class="projects-filter filter-project-form">
                <input type="hidden" name="order" class="order-input" value="<?php echo $order; ?>">
                <input type="hidden" name="type" value="next_project_page">
				<?php if ( $s ): ?>
                    <input type="hidden" name="s" value="<?php echo $s ?>">
				<?php endif; ?>
				<?php if ( $is_admin ): ?>
                    <div class="projects-filter__item">
						<?php if ( $users ): ?>
                            <select name="user_id" class="selectric submit-on-select">
                                <option <?php echo $_user_id == '' ? 'selected' : ''; ?> value="">Виконавець</option>
								<?php foreach ( $users as $user ): $userID = $user->ID;
									$ws_id = carbon_get_user_meta( $userID, 'worksection_id' ) ?>
                                    <option <?php echo $_user_id == $userID ? 'selected' : ''; ?>
                                            value="<?php echo esc_attr( $userID ) ?>">
										<?php echo $user->display_name; ?>
                                    </option>
								<?php endforeach; ?>
                            </select>
						<?php else: ?>
                            Виконавець
						<?php endif; ?>
                    </div>
				<?php endif; ?>
                <div class="projects-filter__item">

                    <select name="orderby" class="selectric trigger-on-select submit-on-select">
                        <option <?php echo $orderby == '' || $order == '' ? 'selected' : ''; ?> value="">
                            Сортувати
                        </option>
                        <option value="activity" <?php echo $orderby == 'activity' && $order == 'desc' ? 'selected' : ''; ?>
                                data-selector=".order-input" data-val="desc">
                            По активності
                        </option>
                        <option value="date" <?php echo $orderby == 'date' && $order == 'desc' ? 'selected' : ''; ?>
                                data-selector=".order-input" data-val="desc">
                            Новіші
                        </option>
                        <option value="date" <?php echo $orderby == 'date' && $order == 'asc' ? 'selected' : ''; ?>
                                data-selector=".order-input" data-val="asc">
                            Старіші
                        </option>
                    </select>
                </div>
				<?php if ( $tags ): ?>
                    <div class="projects-filter__item">
                        <select name="project-tag" class="selectric  submit-on-select">
                            <option selected value="">
                                Теги
                            </option>
							<?php foreach ( $tags as $tag ): ?>
                                <option value="<?php echo $tag->term_id ?>" <?php echo $tag_get == $tag->term_id ? 'selected' : ''; ?> >
									<?php echo $tag->name; ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </div>
				<?php endif; ?>
				<?php if ( $statuses ): ?>
                    <div class="projects-filter__item">
                        <select name="project-status" class="selectric  submit-on-select">
                            <option selected value="">
                                Статус задачі
                            </option>
							<?php foreach ( $statuses as $status => $status_name ): ?>
                                <option value="<?php echo $status ?>" <?php echo $status == $status_get ? 'selected' : ''; ?> >
									<?php echo $status_name ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </div>
				<?php endif; ?>
            </form>
        </div>
        <div class="section-projects-content">
			<?php if ( $is_admin ): ?>
                <div class="section-projects-control">
                    <a href="#"
                       class="button select-all-project <?php echo in_array( '-1', $cockie ) ? 'active' : ''; ?>"
                       data-active="Вибрати всі"
                       data-not-active="Зняти відмітки"
                    >
						<?php echo in_array( '-1', $cockie ) ? 'Зняти відмітки' : 'Вибрати всі'; ?>
                    </a>
                    <a href="#" class="button archive-projects <?php echo count( $cockie ) > 0 ? 'show' : ''; ?>">Архівувати
                        обрані</a>
                </div>
			<?php endif; ?>
            <div class="projects-head">
                <div class="projects-head__column projects-head__date">
                    Дата
                </div>
                <div class="projects-head__column projects-head__title">
                    Назва проекту
                </div>
                <div class="projects-head__column projects-head__performer">
                    Виконавець
                </div>
            </div>
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
</section>
<?php get_footer(); ?>
