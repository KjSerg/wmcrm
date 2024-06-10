<?php
$current_user_id = get_current_user_id();
$var             = variables();
$set             = $var['setting_home'];
$assets          = $var['assets'];
$url             = $var['url'];
$url_home        = $var['url_home'];
absences_action();
if ( ! $current_user_id ) {
	header( 'Location: ' . $url );
	die();
}
$is_admin      = is_current_user_admin();
$time          = time();
$current_year  = date( "Y", $time );
$current_month = (int) date( "m", $time );
$current_date  = date( "d-m-Y", $time );
$users         = get_active_users();
$get_user_id   = $_GET['user_id'] ?? '';
$get_month     = $_GET['month'] ?? $current_month;
$days          = get_day_of_month( $get_month, $current_year );
$reasons       = get_terms( array(
	'taxonomy'   => 'reasons',
	'hide_empty' => false,
) );
get_header();

?>
    <div class="nav">
		<?php if ( $is_admin ): ?>
            <div class="container">
                <div class="nav-list">

                    <a href="<?php echo $url . '?route=users'; ?>" class="nav-list__item link-js">
                <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12"
                                        fill="none">
<path d="M0.174904 5.57196C-0.058285 5.80515 -0.058285 6.19475 0.174904 6.42853L5.55538 11.8228C5.79156 12.059 6.17458 12.059 6.41016 11.8228C6.64634 11.5866 6.64634 11.203 6.41016 10.9668L1.45709 5.99997L6.41076 1.0337C6.64694 0.796928 6.64694 0.41391 6.41076 0.177134C6.17458 -0.0590446 5.79156 -0.0590446 5.55598 0.177134L0.174904 5.57196Z"
      fill="#5C6DF9"/>
</svg></span>Назад до списку працівників
                    </a>
                </div>
            </div>
		<?php endif; ?>
    </div>
    <section class="section days-section calendar-section">
        <div class="container">
            <div class="days-container calendar-container">
                <div class="days-head">
                    <div class="title days-title">
                        Календар відсутностей
                    </div>
                    <form method="get" class="days-head-controls"
                          action="<?php echo get_post_type_archive_link( 'absences' ) ?>">
                        <select name="month" class="selectric submit-on-select ">
							<?php for ( $m = 1; $m <= $current_month; $m ++ ):
								$attr = '';
								if ( $m == $get_month ) {
									$attr = 'selected';
								}
								?>
                                <option
                                        value="<?php echo $m; ?>" <?php echo $attr; ?>>
									<?php echo get_localized_month_name( $m ); ?>, <?php echo $current_year ?>
                                </option>
							<?php endfor; ?>
                        </select>
                        <select name="user_id" class="selectric submit-on-select">
                            <option value="" <?php echo $get_user_id == '' ? 'selected' : ''; ?> >
                                Всі працівники
                            </option>
							<?php foreach ( $users as $user ): if ( ! is__user_admin( $user->ID ) ): ?>
                                <option value="<?php echo $user->ID; ?>" <?php echo $get_user_id == $user->ID ? 'selected' : ''; ?> >
									<?php echo $user->display_name; ?>
                                </option>
							<?php endif; endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="calendar-table-wrapper">
                <div class="calendar-table">
                    <div class="calendar-table-row">
                        <div class="calendar-table-column"></div>
						<?php if ( $days ): foreach ( $days as $_date ):
							$_day = explode( '-', $_date )[0];
							$_day_number = explode( ',', $_date )[1];
							$css_cls = $_day_number == 6 || $_day_number == 7 ? 'weekday' : '';
							?>
                            <div class="calendar-table-column <?php echo $css_cls; ?>"><?php echo $_day; ?></div>
						<?php endforeach; endif; ?>
                    </div>
					<?php if ( $users ): foreach ( $users as $user ): if ( ! is__user_admin( $user->ID ) ):
						$first_name = $user->first_name;
						$last_name = $user->last_name;
						$n = $last_name;
						if ( $first_name ) {
							$n .= ' ' . mb_substr( $first_name, 0, 1 ) . '.';
						}
						?>
                        <div class="calendar-table-row">
                            <div class="calendar-table-column">
                                <div class="calendar-table-user">
									<?php echo $n ?>
                                </div>
                            </div>
							<?php if ( $days ): foreach ( $days as $_date ):
								$_day = explode( '-', $_date )[0];
								$_day_number = explode( ',', $_date )[1];
								$css_cls = $_day_number == 6 || $_day_number == 7 ? 'weekday' : '';
								?>
                                <div class="calendar-table-column <?php echo $css_cls; ?>"></div>
							<?php endforeach; endif; ?>
                        </div>
					<?php endif; endforeach; endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php if ( $is_admin ):
	$args = array(
		'post_type'      => 'absences',
		'posts_per_page' => - 1,
		'post_status'    => 'pending',
	);
	$query     = new WP_Query( $args );
	if ( $query->have_posts() ):
		?>
        <section class="section absences-section">
            <div class="container">
                <div class="absences-title title">
                    Підтвердити відсутність
                </div>
                <div class="absences-list">
					<?php while ( $query->have_posts() ): $query->the_post();
						the_absences();
					endwhile; ?>
                </div>
            </div>
        </section>
	<?php
	endif;
	wp_reset_postdata();
	wp_reset_query();
else: ?>
    <section class="section absences-section">
        <div class="container">
            <div class="absences-title title">
                Додати відсутність
            </div>
            <form method="post" class="form absences-form form-js" novalidate id="add-absences-form">
                <input type="hidden" name="action" value="add_absences">

                <div class="form-row">
					<?php if ( $reasons ): ?>
                        <label class="form-group quarter">
                            <span class="form-group__title"> Причина відсутності</span>
                            <select name="reason" required class="selectric">
                                <option disabled selected>Оберіть причину</option>
								<?php foreach ( $reasons as $reason ): ?>
                                    <option value="<?php echo $reason->term_id; ?>"><?php echo $reason->name; ?></option>
								<?php endforeach; ?>
                            </select>
                        </label>
					<?php endif; ?>
                    <label class="form-group quarter">
                        <span class="form-group__title"> Опис</span>
                        <input type="text" name="text" required
                               value=""
                               placeholder="Введіть пояснення">
                    </label>
                    <label class="form-group quarter">
                        <span class="form-group__title"> Дата з</span>
                        <input type="text" name="date_start" required readonly class="date-input"
                               value="<?php echo $current_date; ?>"
                               placeholder="Оберіть дату">
                    </label>
                    <label class="form-group quarter">
                        <span class="form-group__title"> Дата до</span>
                        <input type="text" name="date_finish" required readonly class="date-input"
                               value="<?php echo $current_date; ?>"
                               placeholder="Оберіть дату">
                    </label>
                </div>
                <div class="form-row">
                    <button class="button" type="submit">
                        Відправити на погодження
                    </button>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>
<?php
get_footer();