<?php
$var             = variables();
$set             = $var['setting_home'];
$assets          = $var['assets'];
$url             = $var['url'];
$logo            = carbon_get_theme_option( 'logo' );
$social_networks = carbon_get_theme_option( 'social_networks' );
$projects_url    = get_post_type_archive_link( 'projects' );
$user_id         = get_current_user_id();
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
<body>
<header class="header <?php echo !is_front_page() ? 'header-inner-page' : ''; ?>">
    <div class="container">
        <div class="header-content">

            <a href="<?php echo $user_id ? $projects_url : $url ?>" class="header-logo link-js">
                <img src="<?php _u( $logo ) ?>" alt="">
            </a>

        </div>
    </div>
</header>
<main class="content <?php echo !is_front_page() ? 'content-inner-page' : ''; ?>">