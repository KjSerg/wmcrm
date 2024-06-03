<?php
function the_login_page() {
	$var        = variables();
	$set        = $var['setting_home'];
	$assets     = $var['assets'];
	$url        = $var['url'];
	$url_home   = $var['url_home'];
	$admin_ajax = $var['admin_ajax'];
	?>
    <section class="section-login" id="section-login">
        <div class="container">
            <div class="section-login-content">
                <div class="title">
                    Необхідно авторизуватись
                </div>
                <form method="post" novalidate class="form login-form form-js" id="login-form">
                    <input type="hidden" name="action" value="login_user">
                    <label class="form-group">
                        <input type="email"
                               name="email"
                               required
                               placeholder="E-mail*"
                               class="form-input"
                               data-reg="[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])">
                    </label>
                    <label class="form-group">
                        <input type="password"
                               name="password"
                               required
                               placeholder="Пароль"
                               class="form-input"
                        >
                    </label>
                    <div class="form-buttons">
                        <button class="form-button">
                            Увійти
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
	<?php
}