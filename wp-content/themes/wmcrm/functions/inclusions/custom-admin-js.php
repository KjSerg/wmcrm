<?php
function custom_admin_js()
{

	$s = 'input[type="hidden"]';
	$a = variables()['admin_ajax'];

	echo "

        <style>
            .cf-complex__groups {
                z-index: 0!important;
            }
        </style>
       <script type='text/javascript' src='https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js' ></script>
       <script>
       var _adminAjax = '$a';
 jQuery(document).ready(function(){
     
     
                                    
                                setTimeout(function () {
                                        jQuery(document).find('.cf-file__inner').each(function () {
                                            var t = jQuery(this);
                                            var id = t.find('$s').eq(0).val();
                                            $.ajax({
                                                type: 'POST',
                                                url: '$a',
                                                data: {
                                                    action: 'get_attach_by_id',
                                                    id: id
                                                }
                                            }).done(function (r) {
                                                t.find('.cf-file__image').attr('src', r)
                        
                                            });
                        
                                        });
                                    }, 1000);
                                
                                
 });
  
    </script>
    ";
}

add_action('admin_footer', 'custom_admin_js');

add_action('admin_footer-edit.php', 'add_status_to_pages');

function add_status_to_pages()
{



    $accommodation_rules = carbon_get_theme_option('accommodation_rules');
    $accommodation_rules = $accommodation_rules?:0;

    echo "
    
    <script>
	jQuery(document).ready( function() {
	    
	    $( '#post-' + $accommodation_rules ).find('strong').append( ' — Страница Правила проживания' );
		
	});
	</script>
    
    ";
}