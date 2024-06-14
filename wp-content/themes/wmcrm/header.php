<?php
$var             = variables();
$set             = $var['setting_home'];
$assets          = $var['assets'];
$url             = $var['url'];
$logo            = carbon_get_theme_option( 'logo' );
$social_networks = carbon_get_theme_option( 'social_networks' );
$projects_url    = get_post_type_archive_link( 'projects' );
$user_id         = get_current_user_id();
$user            = get_user_by( 'id', $user_id );
$avatar          = carbon_get_user_meta( $user_id, 'avatar' );
$is_admin        = is_current_user_admin();
$projects_url    = get_post_type_archive_link( 'projects' );
$discussion_url  = get_post_type_archive_link( 'discussion' );
$is_admin        = is_current_user_admin();
$s               = $_GET['s'] ?? '';
$route           = $_GET['route'] ?? '';
if ( ! $avatar ) {
	$avatar = get_avatar_url( $user_id );
} else {
	$avatar = _u( $avatar, 1 );
}
$msg = absences_action();
?>
<!DOCTYPE html>
<html class="no-js page" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="msapplication-TileColor" content="#656B9B">
    <meta name="theme-color" content="#656B9B">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries-->
    <!-- WARNING: Respond.js doesn't work if you view the page via file://-->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
    <title><?php wp_title(); ?></title>
	<?php wp_head(); ?>
</head>
<body class="<?php echo is_current_user_admin() ? 'user-admin' : ''; ?>">
<div class="notifications">
	<?php echo $msg; ?>
</div>
<header class="header <?php echo ! is_front_page() ? 'header-inner-page' : ''; ?>">
    <div class="container">
        <div class="header-content">
            <div class="header-top">
                <a href="<?php echo $user_id ? $projects_url : $url ?>" class="header-logo link-js">
                    <img src="<?php _u( $logo ) ?>" alt="">
                </a>
				<?php if ( $user_id ): ?>
                    <div class="header-main">
                        <a href="#" class="header-tasks-button">Завдання <span>0</span></a>
                        <a href="<?php echo $discussion_url; ?>" class="header-notification-button link-js">
                            <i class="icon">
                                <svg width="15" height="16" viewBox="0 0 15 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.49656 0C4.22261 0 1.57085 2.44113 1.57085 5.45503V9.70996L0.259792 11.5272C-0.225376 12.2159 -0.0105687 13.133 0.733848 13.5796C0.993098 13.733 1.28938 13.8149 1.59678 13.8183H13.3926C14.2815 13.8149 15.0037 13.15 15 12.3284C15 12.0386 14.9074 11.7556 14.7333 11.5135L13.4223 9.70655V5.45503C13.4223 2.44113 10.7705 0 7.49656 0Z"
                                          fill="white"/>
                                    <path d="M7.49589 16C8.49955 16 9.39211 15.417 9.72914 14.5442H5.26634C5.59966 15.417 6.49222 16 7.49589 16Z"
                                          fill="white"/>
                                </svg>
                            </i> <span>0</span>
                        </a>
                        <a href="<?php echo $url . '?route=profile' ?>" class="header-avatar link-js">
                            <img src="<?php echo $avatar; ?>" alt="">
                        </a>
						<?php the_timer_html() ?>
                        <a href="#" class="burger ">
                            <span></span>
                            <span></span>
                            <span></span>
                        </a>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ( $user_id ): ?>
                <div class="header-bottom">
					<?php
					$menu = ! $is_admin ? wp_nav_menu( [
						'echo'           => false,
						'container'      => '',
						'theme_location' => 'header_menu',
						'menu_class'     => 'header-menu',
						'menu'           => 'Menu 1',
					] ) : wp_nav_menu( [
						'echo'           => false,
						'container'      => '',
						'theme_location' => 'header_menu_admin',
						'menu_class'     => 'header-menu',
						'menu'           => 'Menu admin',
					] );
					echo $menu;
					?>
                    <div class="header-bottom-content">
						<?php if ( $is_admin ): ?>
                            <a href="#event-window" class="add-event-link modal-open">
                                Додати подію
                            </a>
                            <a href="#create-project" class="add-task-link modal-open">
                                + Нова задача
                            </a>
						<?php endif; ?>
                        <form method="get" class="header-form" action="<?php echo $projects_url ?>">
                            <div class="header-form__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16"
                                     fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M10.674 12.1188C9.55357 12.9898 8.14569 13.5084 6.61667 13.5084C2.96238 13.5084 0 10.546 0 6.89178C0 3.23752 2.96238 0.275146 6.61667 0.275146C10.271 0.275146 13.2333 3.23752 13.2333 6.89178C13.2333 8.42083 12.7147 9.82874 11.8437 10.9492L15 14.1055L13.8303 15.2751L10.674 12.1188ZM11.5792 6.89178C11.5792 9.63248 9.35738 11.8543 6.61667 11.8543C3.87596 11.8543 1.65417 9.63248 1.65417 6.89178C1.65417 4.15108 3.87596 1.92931 6.61667 1.92931C9.35738 1.92931 11.5792 4.15108 11.5792 6.89178Z"
                                          fill="#9B9EBE"/>
                                </svg>
                            </div>
                            <input type="text" name="s" placeholder="Пошук..." class="header-form__input"
                                   value="<?php echo $s ?>">
                        </form>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </div>
</header>
<main class="content <?php echo ! is_front_page() ? 'content-inner-page' : ''; ?>">
