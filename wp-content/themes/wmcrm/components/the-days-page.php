<?php
function the_days_page() {
	$var           = variables();
	$set           = $var['setting_home'];
	$assets        = $var['assets'];
	$url           = $var['url'];
	$url_home      = $var['url_home'];
	$time          = time();
	$current_week  = date( "W", $time );
	$current_year  = date( "Y", $time );
	$current_month = (int) date( "m", $time );
	$dates_week    = get_dates_of_week( $current_year, $current_week );
	get_header();
	?>
    <div class="nav">
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
    </div>
    <section class="section days-section">
        <div class="container">
            <div class="days-container">
                <div class="days-head">
                    <div class="title days-title">
                        Робочий день
                    </div>
                    <div class="days-head-controls">
                        <select name="week" class="selectric submit-on-select">
							<?php for ( $w = 1; $w < $current_week; $w ++ ):
								$_dates_week = get_dates_of_week( $current_year, $w );
								$last_day = $_dates_week[6];
								$last_day_m = (int) explode( '-', $last_day )[1];
								if ( $last_day_m == $current_month ):
									?>
                                    <option value="<?php echo $w; ?>">
										<?php echo "Від " . $_dates_week[0] . ' до ' . $last_day; ?>
                                    </option>
								<?php endif; endfor; ?>
                            <option value="<?php echo $current_week; ?>">
								<?php echo "Від " . $dates_week[0] . ' до ' . $dates_week[6]; ?>
                            </option>
                        </select>
                        <select name="month" class="selectric submit-on-select">
							<?php for ( $m = 1; $m <= $current_month; $m ++ ): ?>
                                <option value="<?php echo $m; ?>"><?php echo get_localized_month_name( $m ); ?></option>
							<?php endfor; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>
	<?php
	get_footer();
}