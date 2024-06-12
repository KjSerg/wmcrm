<?php
$current_user_id = get_current_user_id();
$var             = variables();
$set             = $var['setting_home'];
$assets          = $var['assets'];
$url             = $var['url'];
$url_home        = $var['url_home'];
if ( ! $current_user_id ) {
	header( 'Location: ' . $url );
	die();
}
$is_admin      = is_current_user_admin();
$time          = time();
$current_week  = date( "W", $time );
$current_year  = date( "Y", $time );
$current_month = (int) date( "m", $time );
$current_date  = date( "d-m-Y", $time );
$dates_week    = get_dates_of_week( $current_year, $current_week );
$users         = get_active_users();
$get_user_id   = $_GET['user_id'] ?? '';
if ( ! $is_admin ) {
	$get_user_id = $current_user_id;
}
$_get_month         = $_GET['month'] ?? '';
$get_month         = $_GET['month'] ?? $current_month;
$get_week          = $_GET['week'] ?? '';
$active_dates_week = $get_week ? get_dates_of_week( $current_year, $get_week ) : $dates_week;
$active_users      = get_active_users();
$prev_week         = (int) ( $get_week ?: $current_week ) - 1;
$next_week         = (int) ( $get_week ?: $current_week ) + 1;
$prev_week_link    = get_post_type_archive_link( 'costs' ) . '?week=' . $prev_week;
$next_week_link    = get_post_type_archive_link( 'costs' ) . '?week=' . $next_week;
if ( $_GET ) {
	foreach ( $_GET as $key => $value ) {
		if ( $key != 'week' && $value != '' && $key != 'month' ) {
			$prev_week_link .= "&$key=$value";
			$next_week_link .= "&$key=$value";
		}
	}
}
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
    <section class="section days-section">
        <div class="container">
            <div class="days-container">
                <div class="days-controls">
					<?php if ( $prev_week ): ?>
                        <a href="<?php echo $prev_week_link; ?>" class="days-controls__prev link-js">
                            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12"
                                 fill="none">
                                <path d="M0.174904 5.57196C-0.058285 5.80515 -0.058285 6.19475 0.174904 6.42853L5.55538 11.8228C5.79156 12.059 6.17458 12.059 6.41016 11.8228C6.64634 11.5866 6.64634 11.203 6.41016 10.9668L1.45709 5.99997L6.41076 1.0337C6.64694 0.796928 6.64694 0.41391 6.41076 0.177134C6.17458 -0.0590446 5.79156 -0.0590446 5.55598 0.177134L0.174904 5.57196Z"
                                      fill="#5C6DF9"/>
                            </svg>
                        </a>
					<?php endif; ?>
					<?php if ( $next_week <= (int) $current_week ): ?>
                        <a href="<?php echo $next_week_link; ?>" class="days-controls__next link-js">
                            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12"
                                 fill="none">
                                <path d="M0.174904 5.57196C-0.058285 5.80515 -0.058285 6.19475 0.174904 6.42853L5.55538 11.8228C5.79156 12.059 6.17458 12.059 6.41016 11.8228C6.64634 11.5866 6.64634 11.203 6.41016 10.9668L1.45709 5.99997L6.41076 1.0337C6.64694 0.796928 6.64694 0.41391 6.41076 0.177134C6.17458 -0.0590446 5.79156 -0.0590446 5.55598 0.177134L0.174904 5.57196Z"
                                      fill="#5C6DF9"/>
                            </svg>
                        </a>
					<?php endif; ?>
                </div>
                <div class="days-head">
                    <div class="title days-title">
                        Робочий день
                    </div>
                    <form method="get" class="days-head-controls"
                          action="<?php echo get_post_type_archive_link( 'costs' ) ?>">
                        <input type="hidden" name="week" class="week-js" value="">
                        <select class="selectric submit-on-select trigger-on-select">
							<?php for ( $w = 1; $w <= $current_week; $w ++ ):
								$_dates_week = get_dates_of_week( $current_year, $w );
								$last_day = $_dates_week[6];
								$last_day_m = (int) explode( '-', $last_day )[1];
								$attr = '';
								if ( $get_week ) {
									if ( $w == $get_week ) {
										$attr = 'selected';
									}
								} else {
									if ( $current_week == $w ) {
										$attr = 'selected';
									}
								}
								if ( $last_day_m != $get_month ) {
                                    if($_get_month != ''){
	                                    $attr .= ' disabled';
                                    }
								}
								?>
                                <option data-selector=".week-js" data-val="<?php echo $w; ?>"
                                        value="<?php echo $w; ?>" <?php echo $attr; ?>>
									<?php echo "Від " . $_dates_week[0] . ' до ' . $last_day; ?>
                                </option>
							<?php endfor; ?>
                        </select>
                        <select name="month" class="selectric trigger-on-select submit-on-select ">
                            <option disabled <?php echo $_get_month == '' ? 'selected' : ''; ?>>Оберіть місяць</option>
							<?php for ( $m = 1; $m <= $current_month; $m ++ ):
								$attr = '';

								if ( $m == $_get_month ) {
									$attr = 'selected';
								}
								?>
                                <option data-selector=".week-js"
                                        data-val="<?php echo get_first_week_number_month( $current_year, $m ) ?>"
                                        value="<?php echo $m; ?>" <?php echo $attr; ?>>
									<?php echo get_localized_month_name( $m ); ?>
                                </option>
							<?php endfor; ?>
                        </select>
						<?php if ( $users && $is_admin ): ?>
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
						<?php endif; ?>
                    </form>
                </div>
                <div class="days-table-wrapper">
                    <div class="days-table">
                        <div class="days-table-row">
                            <div class="days-table-column"></div>
							<?php if ( $active_dates_week ): foreach ( $active_dates_week as $item ):
								?>
                                <div class="days-table-column"><?php echo convert_date_to_day_format( $item ) ?></div>
							<?php endforeach; endif; ?>
                        </div>
						<?php if ( $active_users ): foreach ( $active_users as $_user ):
							$_userID = $_user->ID;
							if ( ! is__user_admin( $_userID ) ):
								$_attr = '';
								if ( $get_user_id ) {
									if ( $get_user_id != $_userID ) {
										$_attr = 'style="display:none"';
									}
								}
								?>
                                <div class="days-table-row" <?php echo $_attr; ?>>
                                    <div class="days-table-column">
                                        <div class="days-table-user"><?php echo $_user->display_name; ?></div>
                                    </div>
									<?php if ( $active_dates_week ): foreach ( $active_dates_week as $item ): ?>
                                        <div class="days-table-column">
											<?php if ( $cost_id = get_cost_id( array(
												'user_id' => $_userID,
												'date'    => $item
											) ) ) :
												$css_class = '';
												$title_attr = [];
												$costs_sum = carbon_get_post_meta( $cost_id, 'costs_sum_hour' );
												$costs_status = carbon_get_post_meta( $cost_id, 'costs_status' );
												$costs_sum_hour_change = carbon_get_post_meta( $cost_id, 'costs_sum_hour_change' );
												$costs_confirmed = carbon_get_post_meta( $cost_id, 'costs_confirmed' );
												$costs_text_list = carbon_get_post_meta( $cost_id, 'costs_text_list' );
												$costs_sum_seconds = carbon_get_post_meta( $cost_id, 'costs_sum' );
												$res = $costs_sum;
												$changed = false;
												if ( $sum_hour_arr = explode( ':', $costs_sum ) ) {
													$res = $sum_hour_arr[0] . ':' . $sum_hour_arr[1];
												} else {
													$res = $costs_sum;
												}
												if ( $costs_sum_hour_change && $costs_confirmed ) {
													$changed      = $costs_sum_hour_change;
													$css_class    .= ' changed-time';
													$title_attr[] = 'Змінено ' . $costs_confirmed;
												}
												if ( $costs_status == 1 || $costs_status == - 1 ) {
													if ( $current_date != $item ) {
														$log          = carbon_get_post_meta( $cost_id, 'post_data' );
														$css_class    .= ' error-time';
														$title_attr[] = 'Не завершено робочий день(дані можуть бути пораховані некоректно)';
														if ( $log ) {
															$log_arr = explode( '____________________________________________________________________________________________________________________________________________________________________________________', $log );
															if ( $log_arr ) {
																$_s = $log_arr[ array_key_last( $log_arr ) - 1 ];
																if ( ! isJSON( $_s ) ) {
																	$title_attr[] = 'Останній звязок із сервером: ' . $log_arr[ array_key_last( $log_arr ) - 1 ];
																}
															}
														}
													} else {
														$css_class .= ' active-time';
													}
												}
												$title_attr[] = $costs_text_list[0]['text'];
												$string       = '';
												if ( $res ) {
													$string = $changed ? $res . '⮕' . $changed : $res;
												}
												?>
                                                <a href="#"
                                                   data-user-id="<?php echo $_userID; ?>"
                                                   data-date="<?php echo $item; ?>"
                                                   data-cost-id="<?php echo $cost_id; ?>"
                                                   class="days-table-value <?php echo $css_class; ?>"
                                                   title='<?php echo implode( '; ', $title_attr ); ?>'>
													<?php echo $string; ?>
                                                </a>
											<?php endif; ?>
                                        </div>
									<?php endforeach; endif; ?>
                                </div>
							<?php endif; endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
get_footer();