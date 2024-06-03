<?php

add_action('admin_head', 'my_stylesheet');
function my_stylesheet()
{
	wp_enqueue_style("material-design-iconic-font", "https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css");
}