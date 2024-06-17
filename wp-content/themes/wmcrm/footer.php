<?php
$var            = variables();
$set            = $var['setting_home'];
$assets         = $var['assets'];
$url            = $var['url'];
$policy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
$logo           = carbon_get_theme_option( 'logo' );
$preloader      = carbon_get_theme_option( 'preloader' );
$projects_url   = get_post_type_archive_link( 'projects' );
$is_admin       = is_current_user_admin();
$route          = $_GET['route'] ?? '';
$users          = get_active_users();
$tags           = get_terms( array(
	'taxonomy'   => 'tags',
	'hide_empty' => false,
) );
$statuses       = array(
	'publish' => 'В роботі',
	'archive' => 'Завершена',
	'pending' => 'В черзі'
);
?>

</main>



<div class="sounds" style="display:none;">
    <audio id="new-message-sound" autoplay type="audio/mp3" muted
           src="<?php echo $assets ?>/sounds/tone.mp3"></audio>
</div>

<?php if ( $is_admin ): ?>
    <div class="event-window modal-window" id="event-window">
        <div class="modal-window__title">Додати подію</div>
        <div class="modal-window__subtitle">Заповніть форму, щоб створити подію</div>
        <form method="post" class="event-window-form form form-js" id="event-form" novalidate>
            <input type="hidden" name="action" value="create_event">
            <label class="form-group">
                <span class="form-group__title"> Заголовок</span>
                <input type="text" name="title" required
                       value=""
                       placeholder="Введіть назву події">
            </label>
            <label class="form-group">
                <span class="form-group__title"> Опи події</span>
                <textarea name="text" required placeholder="Опис"></textarea>
            </label>
            <div class="event-poll">
                <div class="event-poll-head">
                    <div class="event-poll-head__text">Додати опитування</div>
                    <label class="switch">
                        <input type="checkbox" class="switch-input show-on-change" data-element="#event-poll-body">
                        <span class="switch-element"></span>
                    </label>
                </div>
                <div class="event-poll-body" id="event-poll-body" style="display: none">
                    <label class="form-group">
                        <span class="form-group__title"> Запитання</span>
                        <input type="text" name="question"
                               value=""
                               placeholder="Поставте запитання">
                    </label>
                    <label class="form-group">
                        <span class="form-group__title"> Варіанти відповіді</span>
                        <input type="text" class="copy-on-change" name="answer[]"
                               value=""
                               placeholder="Відповідь">
                    </label>
                    <div class="switchers">
                        <div class="switcher">
                            <div class="switcher__text">Анонімне голосування</div>
                            <label class="switch">
                                <input type="checkbox" name="voting" value="anonymous" class="switch-input">
                                <span class="switch-element"></span>
                            </label>
                        </div>
                        <div class="switcher">
                            <div class="switcher__text">Вибір декілької варіантів</div>
                            <label class="switch">
                                <input type="checkbox" name="type" value="checkbox" checked class="switch-input">
                                <span class="switch-element"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-buttons">
                <button class="form-button button event-window__button">
                    Додати подію
                </button>
            </div>
        </form>
    </div>
    <div class="create-window modal-window" id="new-user">
        <div class="title">
            Новий співробітник
        </div>
        <form class="form form-js create-user-form" id="create-user-form" method="post">
            <input type="hidden" name="action" value="create_new_user">
            <div class="form-row">
                <label class="form-group half">
                    <span class="form-group__title"> Прізвище</span>
                    <input type="text" name="last_name" required
                           value=""
                           placeholder="Введіть  прізвище">
                </label>
                <label class="form-group half">
                    <span class="form-group__title"> Імя</span>
                    <input type="text" name="first_name" required
                           value=""
                           placeholder="Введіть  імя">
                </label>
            </div>
            <div class="form-row">
                <label class="form-group half">
                    <span class="form-group__title"> Email</span>
                    <input type="email" name="email" required
                           value=""
                           data-reg="[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])"
                           placeholder="Введіть  email">
                </label>
                <label class="form-group half">
                    <span class="form-group__title"> Телефон</span>
                    <input type="tel" name="tel"
                           value=""
                           placeholder="Введіть номер телефону">
                </label>
            </div>
            <div class="form-row">
                <label class="form-group half">
                    <span class="form-group__title"> Посада</span>
                    <input type="text" name="position"
                           value=""placeholder="Введіть посаду">
                </label>
                <label class="form-group half">
                    <span class="form-group__title"> День народження</span>
                    <input name="birthday"
                           class="date-input"
                           value="<?php echo '01-01-1995'; ?>"
                           placeholder="День народження">
                </label>
            </div>
            <label class="form-group ">
                <span class="form-group__title"> Worksection id</span>
                <input type="text" name="worksection_id"
                       value="" placeholder="Введіть worksection_id">
            </label>
            <div class="form-buttons">
                <button class="form-button button">
                    Створити нового працівника
                </button>
            </div>
        </form>
    </div>
    <div class="create-window modal-window" id="create-project">
        <div class="title">
            Нова задача
        </div>
        <div class="presets-wrapper">
			<?php the_presets_select() ?>
        </div>
        <form class="form form-js create-form" id="create-form" novalidate method="post">
            <input type="hidden" name="action" value="create_new_project">
            <input type="hidden" name="text" value="" class="value-field">
            <label class="form-group">
                <span class="form-group__title"> Заголовок</span>
                <input type="text" name="title" required
                       value=""
                       placeholder="Введіть назву проєкта">
            </label>
			<?php the_autocomplete_input(
				array(
					'input_name'  => 'parent_id',
					'title'       => 'Батьківський елемент',
					'placeholder' => 'Введіть назву проєкта'
				)
			); ?>
            <div class="form-group">
                <span class="form-group__title">Опис задачі</span>
                <div id="project-editor" class="text"></div>
                <div class="text-editor-list"><?php the_user_select_list(); ?></div>
            </div>
            <div class="form-row">
				<?php if ( $users ): ?>
                    <label class="form-group half">
                        <span class="form-group__title">Спостерігачі</span>
                        <select name="observers[]" multiple class="selectric">
                            <option value="">Спостерігачі</option>
							<?php foreach ( $users as $user ):
								?>
                                <option value="<?php echo esc_attr( $user->ID ) ?>">
									<?php echo esc_html( $user->display_name ) ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </label>
                    <label class="form-group half">
                        <span class="form-group__title">Відповідальні особи</span>
                        <select name="responsible[]" required multiple class="selectric">
                            <option value="">Відповідальні персони</option>
							<?php foreach ( $users as $user ):
								?>
                                <option value="<?php echo esc_attr( $user->ID ) ?>">
									<?php echo esc_html( $user->display_name ) ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </label>
				<?php endif; ?>
            </div>
            <div class="form-row">
				<?php if ( $statuses ): ?>
                    <label class="form-group half">
                        <span class="form-group__title">Статус задачі</span>
                        <select name="post_status" class="selectric">
							<?php foreach ( $statuses as $status => $str ): ?>
                                <option value="<?php echo $status; ?>"><?php echo $str; ?></option>
							<?php endforeach; ?>
                        </select>
                    </label>
				<?php endif; ?>
				<?php if ( $tags ): ?>
                    <label class="form-group half">
                        <span class="form-group__title">Тип задачі</span>
                        <select name="tags[]" multiple class="selectric">
                            <option value="">Теги</option>
							<?php foreach ( $tags as $tag ):
								?>
                                <option value="<?php echo esc_attr( $tag->term_id ) ?>">
									<?php echo esc_html( $tag->name ) ?>
                                </option>
							<?php endforeach; ?>
                        </select>
                    </label>
				<?php endif; ?>
            </div>
            <div class="form-buttons">
                <button type="submit" class="form-button button">
                    Створити
                </button>
                <a href="#" class="form-button button button--bordered save-preset">
                    Зберегти шаблон
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="shadow-window dialog-window" id="shadow-js"></div>

<div class="window-main" id="window-main-js"></div>

<div class="preloader">
    <img src="<?php echo $assets; ?>img/loading.gif" alt="loading.gif">
</div>

<footer class="footer"></footer>

<div class="dialog-window" id="dialog-js">
    <div class="dialog-title"></div>
    <div class="dialog-subtitle"></div>
</div>

<script>
    var adminAjax = '<?php echo $var['admin_ajax']; ?>';
    var projectsUrl = '<?php echo $projects_url; ?>';
</script>

<?php wp_footer(); ?>

</body>

</html>