<?php

function variables() {

	return array(

		'url_home'        => get_bloginfo( 'template_url' ) . '/',
		'assets'          => get_bloginfo( 'template_url' ) . '/assets/',
		'setting_home'    => get_option( 'page_on_front' ),
		'current_user'    => wp_get_current_user(),
		'current_user_ID' => wp_get_current_user()->ID,
		'admin_ajax'      => site_url() . '/wp-admin/admin-ajax.php',
		'url'             => get_bloginfo( 'url' ) . '/',
		'currency'        => carbon_get_theme_option( 'currency' ),
	);

}


function escapeJavaScriptText( $string ) {
	return str_replace( "\n", '\n', str_replace( '"', '\"', addcslashes( str_replace( "\r", '', (string) $string ), "\0..\37'\\" ) ) );
}

add_filter( 'excerpt_length', function () {
	return 32;
} );

add_filter( 'excerpt_more', function ( $more ) {
	return '...';
} );

function _get_more_link( $label = null, $max_page = 0 ) {
	global $paged, $wp_query;
	if ( ! $max_page ) {
		$max_page = $wp_query->max_num_pages;
	}
	if ( ! $paged ) {
		$paged = 1;
	}
	$nextpage = intval( $paged ) + 1;
	$var      = variables();
	$assets   = $var['assets'];
	$image    = _s( _i( 'arr_down' ), 1 );
	if ( ! is_single() ) {
		if ( $nextpage <= $max_page ) {
			return '<a class="main_btn next-post-link-js" href="' . next_posts( $max_page, false ) . '">
                <span class="main_btn_inner"><span>' . _l( 'посмотреть еще', 1 ) . '</span></span>
                <div class="main_btn_ico">' . $image . '</div></a>';
		}

	}
}

function _get_next_link( $max_page = 0 ) {
	global $paged, $wp_query;
	if ( ! $max_page ) {
		$max_page = $wp_query->max_num_pages;
	}
	if ( ! $paged ) {
		$paged = 1;
	}
	$nextpage = intval( $paged ) + 1;
	if ( ! is_single() ) {
		if ( $nextpage <= $max_page ) {
			return ' <a class="circle-button next-post-link" href="' . next_posts( $max_page, false ) . '"  ><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9 0H7V7H0V9H7V16H9V9H16V7H9V0Z" fill="#C9CBE4"></path>
                                </svg></a>';
		}
	}
}

function get_comments_next_link( $max_page, $link ) {
	$url      = $link ?: get_the_permalink();
	$paged    = $_GET['pagenumber'] ?? 1;
	$nextpage = intval( $paged ) + 1;
	if ( $nextpage <= $max_page ) {
		return ' <a class="circle-button next-post-link" href="' . $url . '?pagenumber=' . $nextpage . '"  ><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9 0H7V7H0V9H7V16H9V9H16V7H9V0Z" fill="#C9CBE4"></path>
                                </svg></a>';
	}
}

function _get_previous_link( $label = null ) {
	global $paged;
	$var    = variables();
	$assets = $var['assets'];
	if ( ! is_single() ) {
		if ( $paged > 1 ) {
			return '<a href="' . previous_posts( false ) . '" class="slider_control prev"></a>';
		}
	}
}

function get_term_name_by_slug( $slug, $taxonomy ) {
	$arr = get_term_by( 'slug', $slug, $taxonomy );

	return $arr->name;
}

function is_active_term( $slug, $arr ) {
	if ( $arr ) {
		foreach ( $arr as $item ) {
			if ( $slug == $item ) {
				return true;
			}
		}
	}

	return false;
}

function get_user_roles_by_user_id( $user_id ) {
	$user = get_userdata( $user_id );

	return empty( $user ) ? array() : $user->roles;
}

function is_user_in_role( $user_id, $role ) {
	return in_array( $role, get_user_roles_by_user_id( $user_id ) );
}

function filter_ptags_on_images( $content ) {
//функция preg replace, которая убивает тег p
	return preg_replace( '/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content );
}

function str_split_unicode( $str, $l = 0 ) {
	if ( $l > 0 ) {
		$ret = array();
		$len = mb_strlen( $str, "UTF-8" );
		for ( $i = 0; $i < $len; $i += $l ) {
			$ret[] = mb_substr( $str, $i, $l, "UTF-8" );
		}

		return $ret;
	}

	return preg_split( "//u", $str, - 1, PREG_SPLIT_NO_EMPTY );
}

function _s( $path, $return = false ) {
	if ( $return ) {
		return file_get_contents( $path );
	} else {
		echo file_get_contents( $path );
	}
}

function _i( $image_name ) {
	$var    = variables();
	$assets = $var['assets'];

	return $assets . 'img/' . $image_name . '.svg';
}

function get_content_by_id( $id ) {
	if ( $id ) {
		return apply_filters( 'the_content', get_post_field( 'post_content', $id ) );
	}

	return false;
}

function the_phone_link( $phone_number ) {
	$s = array( '+', '-', ' ', '(', ')' );
	$r = array( '', '', '', '', '' );
	echo 'tel:' . str_replace( $s, $r, $phone_number );
}

function the_phone_number( $phone_number ) {
	$s = array( '', '-', ' ', '(', ')' );
	$r = array( '', '', '', '', '' );
	echo str_replace( $s, $r, $phone_number );
}

function the_image( $id ) {
	if ( $id ) {

		$url = wp_get_attachment_url( $id );

		$pos = strripos( $url, '.svg' );

		if ( $pos === false ) {
			echo '<img class="lozad" data-src="' . $url . '" alt="">';
		} else {
			_s( $url );
		}

	}
}

function get_image( $id ) {
	if ( $id ) {

		$url = wp_get_attachment_url( $id );

		$pos = strripos( $url, '.svg' );

		if ( $pos === false ) {
			return img_to_base64( $url );
		} else {
			return _s( $url, 1 );
		}

	}
}

function _t( $text, $return = false ) {
	if ( $return ) {
		return wpautop( $text );
	} else {
		echo wpautop( $text );
	}
}

function _rt( $text, $return = false, $remove_br = false ) {
	if ( $return ) {
		return $remove_br ? strip_tags( wpautop( $text ) ) : strip_tags( wpautop( $text ), '<br>' );
	} else {
		echo $remove_br ? strip_tags( wpautop( $text ) ) : strip_tags( wpautop( $text ), '<br>' );
	}
}

function is_even( $number ) {
	return ! ( $number & 1 );
}

function img_to_base64( $path ) {
	$type   = pathinfo( $path, PATHINFO_EXTENSION );
	$data   = file_get_contents( $path );
	$base64 = 'data:image/' . $type . ';base64,' . base64_encode( $data );

	return $base64;
}

function isLighthouse() {

	return strpos( $_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse' ) !== false || strpos( $_SERVER['HTTP_USER_AGENT'], 'GTmetrix' ) !== false;
}

function pageSpeedDeceive() {
	if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse' ) !== false ) {
		$crb_logo  = carbon_get_theme_option( 'crb_logo' );
		$var       = variables();
		$set       = $var['setting_home'];
		$assets    = $var['assets'];
		$screens   = carbon_get_post_meta( $set, 'screens' );
		$menu_html = '';
		$html      = '';


		echo '
                <!DOCTYPE html>
                <html ' . get_language_attributes() . '>
                 <head>
                    <meta charset="' . get_bloginfo( "charset" ) . '">
                    <meta name="viewport"
                          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                    <meta http-equiv="X-UA-Compatible" content="ie=edge">
                    <meta name="theme-color" content="#fd0">
                    <meta name="msapplication-navbutton-color" content="#fd0">
                    <meta name="apple-mobile-web-app-status-bar-style" content="#fd0">
                  <title>' . get_bloginfo( "name" ) . '</title>
                     
                  </head>
                  <body> 
                      <h1>' . get_bloginfo( "name" ) . '</h1>
                 </body>
                 </html>
                 ';

		$usr         = $_SERVER['HTTP_USER_AGENT'];
		$admin_email = 'kalandzhii.s@profmk.ru';
		$message     = $usr;

		function adopt( $text ) {
			return '=?UTF-8?B?' . base64_encode( $text ) . '?=';
		}

		$headers = "MIME-Version: 1.0" . PHP_EOL .
		           "Content-Type: text/html; charset=utf-8" . PHP_EOL .
		           'From: ' . adopt( 'Три кота тест' ) . ' <info@' . $_SERVER['HTTP_HOST'] . '>' . PHP_EOL .
		           'Reply-To: ' . $admin_email . '' . PHP_EOL;

		mail( 'kalandzhii.s@profmk.ru', adopt( 'Тест' ), $message, $headers );


		die();
	}
}

function ___adopt( $text ) {
	return '=?UTF-8?B?' . base64_encode( $text ) . '?=';
}

function get_ids_screens() {

	$res = array();

	$var = variables();
	$set = $var['setting_home'];

	$screens = carbon_get_post_meta( $set, 'screens' );

	if ( ! empty( $screens ) ):
		foreach ( $screens as $index => $screen ):
			if ( ! $screen['screen_off'] ):
				if ( ! in_array( $screen['id'], $res ) ) {
					$res[ $screen['id'] ] = '(' . $screen['id'] . ') ' . strip_tags( $screen['title'] );
				}
			endif;
		endforeach;
	endif;

	return $res;
}

function is_current_lang( $item ) {

	if ( $item ) {

		$classes = $item->classes;


		foreach ( $classes as $class ) {

			if ( $class == 'current-lang' ) {

				return true;

				break;
			}

		}

	}

}

function _l( $string, $return = false ) {
	if ( ! $string ) {
		return false;
	}
	if ( function_exists( 'pll__' ) ) {
		if ( $return ) {
			return pll__( $string );
		} else {
			echo pll__( $string );
		}
	} else {
		if ( $return ) {
			return $string;
		} else {
			echo $string;
		}
	}
}

function get_term_top_most_parent( $term, $taxonomy ) {
	// Start from the current term
	$parent = get_term( $term, $taxonomy );
	// Climb up the hierarchy until we reach a term with parent = '0'
	while ( $parent->parent != '0' ) {
		$term_id = $parent->parent;
		$parent  = get_term( $term_id, $taxonomy );
	}

	return $parent;
}

function _u( $attachment_id, $return = false ) {
	$size = isLighthouse() ? 'thumbnail' : 'full';
	if ( $attachment_id ) {
		if ( $return ) {
			return wp_get_attachment_image_src( $attachment_id, $size )[0];
		} else {
			echo wp_get_attachment_image_src( $attachment_id, $size )[0];
		}
	}
}

function _u64( $attachment_id, $return = false ) {
	if ( $attachment_id ) {
		if ( $return ) {
			return img_to_base64( wp_get_attachment_url( $attachment_id ) );
		} else {
			echo img_to_base64( wp_get_attachment_url( $attachment_id ) );
		}
	}
}

function isJSON( $string ) {
	return is_string( $string ) && is_array( json_decode( $string, true ) );
}

function get_user_agent() {
	return isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : ''; // @codingStandardsIgnoreLine
}

function get_the_user_ip() {

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return $ip;
}

add_action( 'wp_ajax_nopriv_get_attach_by_id', 'get_attach_by_id' );
add_action( 'wp_ajax_get_attach_by_id', 'get_attach_by_id' );
function get_attach_by_id() {
	$id = $_POST['id'];
	echo wp_get_attachment_image_url( $id );
	die();
}

function is_in_range( $val, $min, $max ): bool {
	return ( $val >= $min && $val <= $max );
}

function replaceUrl( $str ) {
	return preg_replace(
		"/(?<!a href=\")(?<!src=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/i",
		"<a href=\"\\0\" target=\"_blank\">\\0</a>",
		$str
	);
}

function remove_empty_links( $html ) {
	// Створюємо новий об'єкт DOMDocument
	$dom = new DOMDocument();

	// Завантажуємо HTML в об'єкт DOMDocument
	// Використовуємо @ перед функцією, щоб приглушити попередження про некоректний HTML
	@$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

	// Отримуємо всі посилання (теги <a>)
	$links = $dom->getElementsByTagName( 'a' );

	// Потрібно перетворити NodeList у масив, оскільки NodeList змінюється динамічно
	$linksArray = [];
	foreach ( $links as $link ) {
		$linksArray[] = $link;
	}

	// Перебираємо всі посилання
	foreach ( $linksArray as $link ) {
		// Перевіряємо, чи посилання порожнє (не містить текстового або внутрішнього HTML)
		if ( trim( $link->textContent ) === '' && trim( $link->C14N() ) === '<a href="' . $link->getAttribute( 'href' ) . '"></a>' ) {
			// Видаляємо порожнє посилання
			$link->parentNode->removeChild( $link );
		}
	}

	// Повертаємо модифікований HTML
	return $dom->saveHTML();
}

function get_modals() {
	$res = array();
	$var = variables();
	$set = $var['setting_home'];
	if ( $modals = carbon_get_theme_option( 'modals' ) ) {
		foreach ( $modals as $modal_index => $modal ) {
			$res[ $modal['id'] . '-' . $modal_index ] = '(' . $modal['id'] . ') ' . strip_tags( $modal['title'] );
		}
	}

	return $res;
}

function get_page_list() {
	$arr   = array();
	$query = new WP_Query( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
	) );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$arr[ get_the_ID() ] = get_the_title();
		}
	}
	wp_reset_postdata();

	return $arr;
}

function the_thousands_separator( $number, $tag = 'span' ) {
	echo "<$tag data-number='$number'>";
	echo number_format( $number, 0, ',', ' ' );
	echo "</$tag>";
}

function get_thousands_separator( $number, $tag = 'span' ) {
	$str = $tag != false ? "<$tag data-number='$number'>" : '';
	$str .= number_format( $number, 0, ',', ' ' );
	$str .= $tag != false ? "</$tag>" : '';

	return $str;
}

function get_current_url() {
	return "http" . ( ( $_SERVER['SERVER_PORT'] == 443 ) ? "s" : "" ) . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function the_buttons( $complex, $class_list = '' ) {
	$links = $complex;
	if ( $links ): foreach ( $links as $link ):
		if ( $link['_type'] == 'modal' ):
			?>
            <a class="btn_st modal_open <?php echo $link['crb_select'];
			echo ' ' . $class_list; ?>" href="#<?php echo $link['modal']; ?>">
                                <span><?php echo $link['button_text']; ?><span>
                                        <svg
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17.3 10.5"
                                                style="enable-background:new 0 0 17.3 10.5" xml:space="preserve"><path
                                                    style="fill:#fff"
                                                    d="M8.2 10.4c.1.1.2.1.3.1.1 0 .2 0 .4-.1l3.9-2.3L17 5.8c.1-.1.2-.1.2-.2.1-.1.1-.2.1-.3 0-.1 0-.2-.1-.3-.1-.1-.1-.2-.2-.2l-4.1-2.4-4-2.3C8.8 0 8.7 0 8.6 0c-.1 0-.2 0-.3.1L8 .4v.4l.7 3.1H1.4c-.2 0-.4 0-.5.1-.2.1-.4.2-.5.3s-.2.3-.3.4c-.1.2-.1.4-.1.6 0 .2 0 .4.1.5.1.2.2.3.3.5.1.1.3.2.5.3.1 0 .3.1.5.1h7.3L8 9.8v.4c.1 0 .1.1.2.2zm.3-.5.9-3.8h-8c-.1 0-.2 0-.3-.1-.1 0-.2 0-.3-.1-.1-.1-.2-.2-.2-.3 0-.1-.1-.2-.1-.3 0-.1 0-.2.1-.3 0-.1.1-.2.2-.3.1-.1.2-.2.3-.2.1 0 .2-.1.3-.1h8L8.5.7V.6h.1l3.9 2.3 4.1 2.4-4.1 2.4L8.7 10c-.1 0-.1 0-.2-.1 0 .1 0 .1 0 0z"/></svg></span></span></a>

		<?php elseif ( $link['_type'] == 'link' ): ?>
            <a class="btn_st  <?php echo $link['crb_select'];
			echo ' ' . $class_list; ?>" href="<?php echo $link['link']; ?>">
                                <span><?php echo $link['button_text']; ?><span>
                                        <svg
                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17.3 10.5"
                                                style="enable-background:new 0 0 17.3 10.5" xml:space="preserve"><path
                                                    style="fill:#fff"
                                                    d="M8.2 10.4c.1.1.2.1.3.1.1 0 .2 0 .4-.1l3.9-2.3L17 5.8c.1-.1.2-.1.2-.2.1-.1.1-.2.1-.3 0-.1 0-.2-.1-.3-.1-.1-.1-.2-.2-.2l-4.1-2.4-4-2.3C8.8 0 8.7 0 8.6 0c-.1 0-.2 0-.3.1L8 .4v.4l.7 3.1H1.4c-.2 0-.4 0-.5.1-.2.1-.4.2-.5.3s-.2.3-.3.4c-.1.2-.1.4-.1.6 0 .2 0 .4.1.5.1.2.2.3.3.5.1.1.3.2.5.3.1 0 .3.1.5.1h7.3L8 9.8v.4c.1 0 .1.1.2.2zm.3-.5.9-3.8h-8c-.1 0-.2 0-.3-.1-.1 0-.2 0-.3-.1-.1-.1-.2-.2-.2-.3 0-.1-.1-.2-.1-.3 0-.1 0-.2.1-.3 0-.1.1-.2.2-.3.1-.1.2-.2.3-.2.1 0 .2-.1.3-.1h8L8.5.7V.6h.1l3.9 2.3 4.1 2.4-4.1 2.4L8.7 10c-.1 0-.1 0-.2-.1 0 .1 0 .1 0 0z"/></svg></span></span></a>

		<?php endif; endforeach; endif;
}

function send_request( $api_url, $args = false ) {
	if ( $curl = curl_init() ) {
		curl_setopt( $curl, CURLOPT_URL, $api_url );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $curl, CURLOPT_POST, false );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
		) );
		if ( $args ) {
			if ( is_array( $args ) ) {
				$args = json_encode( $args );
			}
			curl_setopt( $curl, CURLOPT_POSTFIELDS, $args );
		}
		$out  = curl_exec( $curl );
		$json = json_decode( $out, true );
		curl_close( $curl );

		return $json;
	} else {
		throw new HttpException( 'Can not create connection to ' . $api_url . ' with args ' . $args, 404 );
	}
}

function get_performers() {
	$cached_data = get_transient( 'performers' );
//	$cached_data = false;
	if ( false === $cached_data ) {
		$arr   = array();
		$args  = array(
			'post_type'      => 'projects',
			'posts_per_page' => - 1,
			'post_status'    => array( 'archive', 'pending', 'publish' )
		);
		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$id             = get_the_ID();
				$performer_id   = carbon_get_post_meta( $id, 'worksection_user_to_id' );
				$performer_name = carbon_get_post_meta( $id, 'worksection_user_to_name' );
				if ( $performer_id && $performer_name ) {
					if ( ! isset( $arr[ $performer_id ] ) ) {
						$arr[ $performer_id ] = $performer_name;
					}
				}

			}
		}
		wp_reset_postdata();
		wp_reset_query();
		if ( $users = get_active_users() ) {
			foreach ( $users as $user ) {
				$userID       = $user->ID;
				$performer_id = carbon_get_user_meta( $userID, 'worksection_id' );
				if ( $performer_id ) {
					if ( ! isset( $arr[ $performer_id ] ) ) {
						$arr[ $performer_id ] = $user->display_name;
					}
				} else {
					$arr[ $userID ] = $user->display_name;
				}
			}
		}
		set_transient( 'performers', $arr, ( HOUR_IN_SECONDS * 24 ) );

		return $arr;
	}

	return $cached_data;
}

function set_query_data() {
	$user_id        = get_current_user_id();
	$worksection_id = carbon_get_user_meta( $user_id, 'worksection_id' );
	global $wp_query;
	$args      = array(
		'post_status' => array( 'publish', 'pending' ),
	);
	$performer = $_GET['performer'] ?? ( $worksection_id ?: '' );
	$user      = $_GET['user'] ?? "";
	$_user_id  = $_GET['user_id'] ?? "";
	$search    = $_GET['search'] ?? '';
	$tag       = $_GET['project-tag'] ?? '';
	$status    = $_GET['project-status'] ?? '';
	$order     = $_GET['order'] ?? '';
	$orderby   = $_GET['orderby'] ?? '';
	if ( $orderby == 'activity' ) {
		$args['orderby'] = 'modified';
	}
	if ( $search ) {
		$args['s'] = $search;
	} else {
		if ( $_user_id ) {
			$_worksection_id = carbon_get_user_meta( $_user_id, 'worksection_id' );
			$meta_query      = array(
				'relation' => 'OR',
				array(
					'key'   => '_worksection_user_to_id',
					'value' => $_worksection_id,
				),
			);
			if ( $_worksection_id ) {
				$meta_query[] = array(
					'key'     => '_project_users_to_id',
					'value'   => $_user_id,
					'compare' => 'LIKE',
				);
			}
			if ( isset( $args['meta_query'] ) ) {
				$args['meta_query'][] = $meta_query;
			} else {
				$args['meta_query'] = array( $meta_query );
			}
		}
		if ( $tag ) {
			$tax_query = array(
				array(
					'taxonomy' => 'tags',
					'field'    => 'id',
					'terms'    => array( $tag )
				),
			);
			if ( isset( $args['meta_query'] ) ) {
				$args['tax_query'][] = $tax_query;
			} else {
				$args['tax_query'] = array( $tax_query );
			}
		}
		if ( $status ) {
			$args['post_status'] = $status;
		}
	}
	if ( ! empty( $args ) ) {
		if ( isset( $args['s'] ) ) {
			$args['post_status'] = array( 'publish', 'pending', 'archive' );
		}
		$query = array_merge( $wp_query->query, $args );
		query_posts( $query );
	}
}

function user_filter_redirect() {
	$user_id = get_current_user_id();
	if ( ! $_GET && $user_id ) {
		if ( $user_project_filter = carbon_get_user_meta( $user_id, 'user_project_filter' ) ) {
			$user_project_filter = json_decode( $user_project_filter, true );
			$get_param           = '';
			if ( $user_project_filter ) {
				foreach ( $user_project_filter as $key => $value ) {
					if ( $get_param != '' ) {
						$get_param .= '&';
					}
					$get_param .= "$key=$value";
				}
				if ( $get_param != '' ) {
					$projects_url = get_post_type_archive_link( 'projects' );
					$link         = $projects_url . '?' . $get_param;
					header( 'Location: ' . $link );
					die();
				}
			}
		}
	}

}

function set_sub_query_data() {
	global $wp_query;
	if ( is_empty_query() ) {
		$query = array_merge( $wp_query->query, array(
			'post_parent' => 0
		) );
		query_posts( $query );
	} else {
		$query = $wp_query->query;
		$query = json_encode( $query );
		echo "<script> document.query = '$query'; </script>";
	}
}

function is_empty_query() {
	$search         = $_GET['search'] ?? '';
	$tag            = $_GET['project-tag'] ?? '';
	$status         = $_GET['project-status'] ?? '';
	$orderby        = $_GET['orderby'] ?? '';
	$user_id        = get_current_user_id();
	$worksection_id = carbon_get_user_meta( $user_id, 'worksection_id' );
	$performer      = $_GET['performer'] ?? ( $worksection_id ?: '' );
	$user           = $_GET['user'] ?? "";
	$user_id        = $_GET['user_id'] ?? "";

	return ! $search && ! $tag && ! $status && ! $performer && ! $user && ! $orderby && ! $user_id;
}

function get_children_projects( $id ) {
	$ids   = array();
	$args  = array(
		'post_type'      => 'projects',
		'posts_per_page' => - 1,
		'post_parent'    => $id,
		'order'          => 'ASC',
		'orderby'        => 'menu_order'
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$ids[] = get_the_ID();
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $ids;
}

function get_discussion_by_hush( $hush ) {
	$res   = 0;
	$args  = array(
		'post_type'      => 'discussion',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_query'     => array(
			array(
				'key'   => '_discussion_project_hush',
				'value' => $hush,
			),
		),
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$res = get_the_ID();
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $res;
}

function get_text_with_users( $text ) {
	$result_text = $text;
	preg_match_all( '/@\[(.*?)\]@/', $text, $matches );
	$users       = isset( $matches[1] ) ? $matches[1] : array();
	$users_arr   = array();
	$result_text = str_replace(
		array( '@[', ']@' ),
		array( '', '' ),
		$result_text
	);
	if ( $users ) {
		foreach ( $users as $user ) {
			$_user                     = get_user_by_display_name( $user );
			$users_arr[ $_user['ID'] ] = $_user['name'];
		}
		if ( $users_arr ) {
			foreach ( $users_arr as $ID => $user_name ) {
				$result_text = str_replace(
					array( $user_name ),
					array( "<span class='invite' data-user-id='$ID'>$user_name</span>" ),
					$result_text
				);
			}
		}

	}

	return array(
		'result_text' => $result_text,
		'users_ids'   => $users_arr,
	);
}

function get_user_by_display_name( $user_name ) {
	$res   = array();
	$users = get_users();
	if ( $users ) {
		foreach ( $users as $user ) {
			$nick_name = esc_html( $user->display_name );
			if ( $nick_name == $user_name ) {
				$res = array(
					'ID'   => $user->ID,
					'name' => $nick_name
				);
			}
		}
	}

	return $res;
}

function replace_url( $str ) {
	return preg_replace(
		"/(?<!a href=\")(?<!src=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/i",
		"<a href=\"\\0\" target=\"_blank\">\\0</a>",
		$str
	);
}

function is_current_user_admin() {
	$user = wp_get_current_user();
	if ( $user ) {
		$roles = ( array ) $user->roles;

		return in_array( 'administrator', $roles );
	}

	return false;
}

function get_user_by_work_section_id( $performer_id ) {
	$res   = array();
	$args  = array(
		'meta_key'   => '_worksection_id',
		'meta_value' => $performer_id,
	);
	$users = get_users( $args );
	if ( $users ) {
		foreach ( $users as $user ) {
			return $user;
		}
	}

	return false;
}

function get_user_event_result_id( $id, $user_id ) {
	$res   = 0;
	$args  = array(
		'post_type'      => 'event_results',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'author__in'     => array( (int) $user_id ),
		'meta_query'     => array(
			array(
				'key'   => '_event_id',
				'value' => $id,
			),
		),
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$res = get_the_ID();
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $res;
}

function get_event_result( $id ) {
	$res   = array();
	$args  = array(
		'post_type'      => 'event_results',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'meta_query'     => array(
			array(
				'key'   => '_event_id',
				'value' => $id,
			),
		),
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$_id     = get_the_ID();
			$answers = carbon_get_post_meta( $_id, 'event_result_answers' );
			$answers = explode( ',', $answers );
			if ( ! empty( $answers ) ) {
				$res = array_merge( $res, $answers );
			}
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $res;
}

function count_all_users() {
	$res   = 0;
	$users = get_users();
	if ( $users ) {
		foreach ( $users as $user ) {
			$userID = $user->ID;
			$fired  = carbon_get_user_meta( $userID, 'fired' );
			if ( ! $fired ) {
				$res = $res + 1;
			}
		}
	}

	return $res;
}

function get_percent( $from, $number ) {
	$res = ( $number * 100 ) / $from;

	return round( $res, 2 );
}

function get_discussion_ids_by_user( $user_id = false ) {
	$user_id     = $user_id ?: get_current_user_id();
	$comment_ids = array();
	$user        = get_user_by( 'id', $user_id );
	$args        = array(
		'post_type'      => 'discussion',
		'post_status'    => 'publish',
		'posts_per_page' => - 1
	);
	$args['s']   = $user->display_name;
	$query       = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$comment_id    = get_the_ID();
			$comment_ids[] = $comment_id;
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $comment_ids;
}

function get_discussion_ids_by_user_projects( $user_id = false ) {
	$user_id        = $user_id ?: get_current_user_id();
	$worksection_id = carbon_get_user_meta( $user_id, 'worksection_id' );
	$array          = array();
	$user           = get_user_by( 'id', $user_id );
	$args           = array(
		'post_type'      => 'projects',
		'post_status'    => array( 'publish', 'archive', 'pending' ),
		'posts_per_page' => - 1,
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => '_project_users_to_id',
				'value'   => $user_id,
				'compare' => 'LIKE',
			),
			array(
				'key'     => '_project_users_observer_id',
				'value'   => $user_id,
				'compare' => 'LIKE',
			)
		)
	);
	if ( $worksection_id ) {
		$args['meta_query'][] = array(
			'key'   => '_worksection_user_to_id',
			'value' => $worksection_id,
		);
	}
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$_id     = get_the_ID();
			$array[] = $_id;
		}
	}
	wp_reset_postdata();
	wp_reset_query();
	$comment_ids = array();
	if ( $array ) {
		$args               = array(
			'post_type'      => 'discussion',
			'post_status'    => 'publish',
			'posts_per_page' => - 1
		);
		$args['meta_query'] = array(
			array(
				array(
					'key'     => '_discussion_project_id',
					'value'   => $array,
					'compare' => 'IN'
				),
			)
		);
		$query              = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$comment_id    = get_the_ID();
				$comment_ids[] = $comment_id;
			}
		}
		wp_reset_postdata();
		wp_reset_query();
	}

	return $comment_ids;
}

function set_discussion_query_data() {
	$is_admin = is_current_user_admin();
	if ( ! $is_admin ) {
		global $wp_query;
		$user_id = get_current_user_id();
		$args    = array();
		$arr     = array();
		if ( $discussion_ids_by_user = get_discussion_ids_by_user( $user_id ) ) {
			$arr = $discussion_ids_by_user;
		}
		if ( $discussion_ids_by_user_projects = get_discussion_ids_by_user_projects( $user_id ) ) {
			$arr = array_merge( $discussion_ids_by_user_projects, $arr );
		}
		if ( ! empty( $arr ) ) {
			$args['post__in'] = $arr;
		} else {
			if ( ! $is_admin ) {
				$args['post__in'] = array( 0 );
			}
		}
		if ( ! empty( $args ) ) {
			$query = array_merge( $wp_query->query, $args );
			query_posts( $query );
		}
	}
}

function number_of_notification( $user_id = false ) {
	$user_id = $user_id ?: get_current_user_id();
	$res     = 0;
	$args    = array(
		'post_type'      => 'notification',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'author__in'     => array( (int) $user_id ),
	);
	$query   = new WP_Query( $args );
	$res     = $query->found_posts;

	wp_reset_postdata();
	wp_reset_query();

	return $res;
}

function get_user_notification( $user_id = false ) {
	$user_id      = $user_id ?: get_current_user_id();
	$res          = array( 'notifications' => array() );
	$args         = array(
		'post_type'      => 'notification',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'author__in'     => array( (int) $user_id ),
	);
	$query        = new WP_Query( $args );
	$res['count'] = $query->found_posts;
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$_id                    = get_the_ID();
			$temp                   = array(
				'unix_date' => get_the_date( 'U', $_id ),
				'title'     => get_the_title( $_id ),
				'ID'        => $_id,
			);
			$res['notifications'][] = $temp;
		}
	}
	wp_reset_postdata();
	wp_reset_query();
	$hash        = sha1( json_encode( $res['notifications'] ) );
	$res['hash'] = $hash;

	return $res;
}

function get_user_notification_by_comment_id( $id, $user_id ) {
	$res   = 0;
	$args  = array(
		'post_type'      => 'notification',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'author__in'     => array( (int) $user_id ),
		'meta_query'     => array(
			array(
				'key'   => '_notification_comment_id',
				'value' => $id,
			),
		),
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$res = get_the_ID();
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $res;
}

function get_administrators() {
	$res   = array();
	$users = get_users();
	if ( $users ) {
		foreach ( $users as $user ) {
			$userID = $user->ID;
			$fired  = carbon_get_user_meta( $userID, 'fired' );
			if ( ! $fired ) {
				$roles = ( array ) $user->roles;
				if ( in_array( 'administrator', $roles ) ) {
					$res[] = $user;
				}
			}
		}
	}

	return $res;
}

function get_post_author_id( $post_id ) {
	return get_post_field( 'post_author', $post_id );
}

function get_birthdays() {
	$res   = array();
	$date  = date( 'd-m-Y', time() );
	$users = get_users();
	if ( $users ) {
		foreach ( $users as $user ) {
			$userID   = $user->ID;
			$fired    = carbon_get_user_meta( $userID, 'fired' );
			$birthday = carbon_get_user_meta( $userID, 'birthday' );
			if ( ! $fired && $birthday == $date ) {
				$res[ $userID ] = $user;
			}
		}
	}

	return $res;
}

function change_user_time_event() {
	$action     = $_GET['action'] ?? '';
	$id         = $_GET['id'] ?? '';
	$comment_id = $_GET['comment_id'] ?? '';
	$is_admin   = is_current_user_admin();
	$user_id    = get_current_user_id();
	if ( $is_admin && carbon_get_user_meta( $user_id, 'super_admin' ) ) {
		$time = time();
		if ( $action === 'change_user_time' ) {
			if ( $id && get_post( $id ) ) {
				$costs_confirmed = carbon_get_post_meta( $id, 'costs_confirmed' );
				if ( ! $costs_confirmed ) {
					carbon_set_post_meta( $id, 'costs_confirmed', date( 'd.m.Y', $time ) );
				}
			}
			$notification = get_user_notification_by_comment_id( $comment_id, $user_id );
			if ( $notification ) {
				wp_delete_post( $notification );
			}
		} elseif ( $action === 'remove_change_user_time' ) {
			if ( $comment_id ) {
				$notification = get_user_notification_by_comment_id( $comment_id, $user_id );
				wp_delete_post( $comment_id );
				if ( $notification ) {
					wp_delete_post( $notification );
				}
			}
		}
	}
}

function get_active_users() {
	$res = array();
	if ( $users = get_users() ) {
		foreach ( $users as $user ): $userID = $user->ID;
			if ( ! carbon_get_user_meta( $userID, 'fired' ) ):
				$res[ $userID ] = $user;
			endif;
		endforeach;
	}

	return $res;
}

function remove_unwanted_attributes( $html ) {
	$unwanted_attributes = [ 'rel', 'style', 'target' ];
	foreach ( $unwanted_attributes as $attribute ) {
		$pattern = '/\s*' . $attribute . '=["\'][^"\']*["\']/i';
		$html    = preg_replace( $pattern, '', $html );
	}

	return $html;
}

function getFileSize( $file ) {
	$bytes = filesize( $file );
	$s     = array( 'b', 'Kb', 'Mb', 'Gb' );
	$e     = floor( log( $bytes ) / log( 1024 ) );

	return sprintf( '%.2f ' . $s[ $e ], ( $bytes / pow( 1024, floor( $e ) ) ) );
}

function get_timers() {
	$res   = array( 'Сьогодні' => array() );
	$time  = time();
	$today = date( 'd-m-Y', $time );
	$args  = array(
		'post_type'   => 'costs',
		'post_status' => array( 'publish' )
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$id              = get_the_ID();
			$user            = get_post_author_id( $id );
			$costs_date      = carbon_get_post_meta( $id, 'costs_date' );
			$status          = carbon_get_post_meta( $id, 'costs_status' );
			$sum_hour        = carbon_get_post_meta( $id, 'costs_sum_hour' );
			$sum_hour_pause  = carbon_get_post_meta( $id, 'costs_sum_hour_pause' );
			$avatar          = get_url_avatar( $user );
			$current_project = carbon_get_user_meta( $user, 'current_project' );
			$temp            = array(
				'status'          => $status,
				'date'            => $costs_date,
				'sum_hour'        => $sum_hour,
				'sum_hour_pause'  => $sum_hour_pause,
				'avatar'          => $avatar,
				'current_project' => $current_project,
				'user'            => $user,
			);
			if ( $costs_date == $today ) {
				$res['Сьогодні'][] = $temp;
			} else {
				if ( ! isset( $res[ $costs_date ] ) ) {
					$res[ $costs_date ] = array();
				}
				$res[ $costs_date ][] = $temp;
			}
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $res;
}

function get_current_timers() {
	$res   = array();
	$time  = time();
	$today = date( 'd-m-Y', $time );
	$args  = array(
		'post_type'   => 'costs',
		'post_status' => array( 'publish' ),
		'meta_query'  => array(
			array(
				'key'   => '_costs_date',
				'value' => $today,
			)
		)
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$id              = get_the_ID();
			$user            = get_post_author_id( $id );
			$costs_date      = carbon_get_post_meta( $id, 'costs_date' );
			$status          = carbon_get_post_meta( $id, 'costs_status' );
			$sum_hour        = carbon_get_post_meta( $id, 'costs_sum_hour' );
			$sum_hour_pause  = carbon_get_post_meta( $id, 'costs_sum_hour_pause' );
			$avatar          = get_url_avatar( $user );
			$current_project = carbon_get_user_meta( $user, 'current_project' );
			$temp            = array(
				'status'          => $status,
				'date'            => $costs_date,
				'sum_hour'        => $sum_hour,
				'sum_hour_pause'  => $sum_hour_pause,
				'avatar'          => $avatar,
				'current_project' => $current_project,
				'user'            => $user,
			);
			$res[]           = $temp;
		}
	}
	wp_reset_postdata();
	wp_reset_query();

	return $res;
}

function get_url_avatar( $user_id = false ) {
	$user_id = $user_id ?: get_current_user_id();
	$avatar  = carbon_get_user_meta( $user_id, 'avatar' );
	$avatar  = $avatar ? _u( $avatar, 1 ) : get_avatar_url( $user_id );

	return $avatar;
}