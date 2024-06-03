<?php
 add_action('after_setup_theme',
     function () {
         register_nav_menus(
             array(
                 'header_menu' => 'Меню в шапке',
                 'footer_menu' => 'Меню в подвале',
             )
         );
     }
 );