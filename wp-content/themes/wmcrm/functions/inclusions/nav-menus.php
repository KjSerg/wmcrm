<?php
 add_action('after_setup_theme',
     function () {
         register_nav_menus(
             array(
                 'header_menu' => 'Меню в шапці',
                 'header_menu_admin' => 'Меню в шапці адміністратора',
             )
         );
     }
 );