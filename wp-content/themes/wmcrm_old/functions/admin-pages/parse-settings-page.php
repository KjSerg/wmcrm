<?php

add_action( 'admin_enqueue_scripts', 'custom_admin_assets' );

function custom_admin_assets() {

	wp_enqueue_style( 'custom-admin-styles', get_template_directory_uri() . '/assets/css/admin.css' );

	wp_enqueue_script( 'custom-admin-scripts', get_template_directory_uri() . '/assets/js/admin.js', array( 'jquery' ), null, true );

}

add_action( 'admin_menu', 'my_custom_menu_page' );

function my_custom_menu_page() {
	add_menu_page(
		'Налаштування парсингу',
		'Налаштування парсингу',
		'manage_options',
		'custom_parser_page',
		'custom_parser_html',
		'dashicons-rest-api',
		20
	);
}


function custom_parser_html() {
	$var        = variables();
	$admin_ajax = $var['admin_ajax'];
	$assets     = $var['assets'];
	?>
    <script>
        var admin_ajax = '<?php echo $admin_ajax; ?>';
        var preloader = '<?php echo $assets; ?>img/loading.gif';
    </script>
    <div class="preloader">
        <img src="<?php echo $assets; ?>img/loading.gif" alt="loading.gif">
    </div>
    <div class="wrap">
        <h2>Хай буде парсинг</h2>
        <hr>
        <h3>Получити проекти</h3>
        <form action="<?php echo $admin_ajax; ?>" method="post" class="js-parser-form">
            <input type="hidden" name="action" value="start_parsing">
            <div class="input-wrapper">
                <p><label for="api-key-1">API key</label></p>
                <input type="text" id="api-key-1" name="api_key" required class="regular-text"
                       value="f7c9452603952b660545a60aeeda8224">
            </div>
            <div class="input-wrapper">
                <p><label for="domain-1">Домин</label></p>
                <input type="url" id="domain-1" name="domain" required class="regular-text"
                       value="https://webmosaica.worksection.ua/">
            </div>
            <div class="button-wrapper">
                <button class="button button-primary button-large">
                    Положити сервер!
                </button>

            </div>
        </form>
        <hr>
        <h3>Получити коментарі</h3>
        <form action="<?php echo $admin_ajax; ?>" method="post" class="js-parser-form ">
            <input type="hidden" name="action" value="get__comments">
            <input type="hidden" name="connect_id" value="<?php
			$bytes = random_bytes( 5 );
			echo bin2hex( $bytes );
			?>">
            <div class="input-wrapper">
                <p><label for="api-key">API key</label></p>
                <input type="text" id="api-key" name="api_key" required class="regular-text"
                       value="f7c9452603952b660545a60aeeda8224">
            </div>
            <div class="input-wrapper">
                <p><label for="domain">Домин</label></p>
                <input type="url" id="domain" name="domain" required class="regular-text"
                       value="https://webmosaica.worksection.ua/">
            </div>
            <div class="input-wrapper">
                <p><label for="offset">№ початкового проекта</label></p>
                <input type="number" id="offset" name="offset" required class="regular-text"
                       value="<?php echo $_COOKIE['project_number'] ?? '0'; ?>">
            </div>
            <div class="input-wrapper">
                <p><label for="number">Кількість проектів</label></p>
                <input type="number" id="number" name="number" required class="regular-text"
                       value="3778">
            </div>
            <div class="input-wrapper">
                <p>
                    <input type="checkbox" id="get-status" name="get-status">
                    <label for="get-status">Получати проміжковий(бета) статус</label>
                </p>

            </div>
            <div class="input-wrapper">
                <p><input type="checkbox" id="is-infinite" name="is-infinite"><label for="is-infinite">Запустити цикл
                        (при закінчені скрипта він почнеться заново)</label></p>

            </div>
            <div class="button-wrapper">
                <button class="button button-primary button-large">
                    Получити коментарі
                </button>
            </div>
        </form>

        <hr>
        <hr>
        <div class="result" style="border: 1px dotted #cecece"></div>
        <hr>
        <br>
        <br>
        <br>
        <br>
        <br>
        <h2> Оновити проекти</h2>
        <hr>
        <a class="button button-primary button-large send-request"
           href="<?php echo $admin_ajax; ?>?action=update_projects&api_key=f7c9452603952b660545a60aeeda8224&domain=https://webmosaica.worksection.ua/">
            Оновити проекти (бета)
        </a>
        <h2>Видалити всі коментарі</h2>
        <hr>
        <a class="button button-primary button-large send-request"
           href="<?php echo $admin_ajax; ?>?action=clear_all_comments">
            Видалити всі коментарі
        </a>
    </div>
	<?php
}
