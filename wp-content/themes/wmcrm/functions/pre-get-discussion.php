<?php
function modify_discussions_archive_sql_where( $where, $query ) {
	// 1. Перевіряємо, що це правильний запит:
	//    - Не в адмін-панелі
	//    - Головний запит сторінки (не віджети, не меню)
	//    - Архів постів типу 'discussion'
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive('discussion') ) {
		return $where; // Якщо ні, нічого не робимо
	}

	global $wpdb;
	$user_id = get_current_user_id();

	// Якщо користувач не залогінений, він нічого не має бачити
	if ( $user_id === 0 ) {
		return " AND 1=0"; // Повертаємо гарантовано порожній результат
	}

	// Використовуємо вашу функцію для перевірки на адміна
	$is_admin = is_current_user_admin();

	// Якщо це адмін, ми не застосовуємо жодних фільтрів. Він бачить все.
	if ( $is_admin ) {
		return $where;
	}

	// --- БУДУЄМО SQL-УМОВИ ---

	// Умова 1: Користувач згаданий в таксономії `involved_users`
	// Ми генеруємо SQL для цієї умови напряму, це надійніше, ніж створювати WP_Query
	$term = get_term_by('slug', (string) $user_id, 'involved_users');
	$tax_clause = '';
	if ($term) {
		$tax_clause = $wpdb->prepare(
			"{$wpdb->posts}.ID IN (
                SELECT tr.object_id FROM {$wpdb->term_relationships} AS tr
                WHERE tr.term_taxonomy_id = %d
            )",
			$term->term_taxonomy_id
		);
	}

	// Умова 2: Обговорення належить до проекту користувача
	$user_project_ids = get_user_projects_ids( $user_id );
	$meta_clause = '';
	if ( ! empty( $user_project_ids ) ) {
		// Перетворюємо масив ID в рядок для SQL-запиту 'IN'
		$project_ids_string = implode( ',', array_map( 'absint', $user_project_ids ) );
		$meta_clause = $wpdb->prepare(
			"{$wpdb->posts}.ID IN (
                SELECT pm.post_id FROM {$wpdb->postmeta} AS pm
                WHERE pm.meta_key = '_discussion_project_id' AND pm.meta_value IN ({$project_ids_string})
            )"
		);
	}

	// --- ОБ'ЄДНУЄМО УМОВИ ---

	$custom_where = '';

	// Якщо існують обидві умови, об'єднуємо їх через OR
	if ( ! empty( $tax_clause ) && ! empty( $meta_clause ) ) {
		$custom_where = " AND ( ( {$tax_clause} ) OR ( {$meta_clause} ) )";
		// Якщо існує тільки одна з умов
	} elseif ( ! empty( $tax_clause ) ) {
		$custom_where = " AND ( {$tax_clause} )";
	} elseif ( ! empty( $meta_clause ) ) {
		$custom_where = " AND ( {$meta_clause} )";
		// Якщо для користувача немає ні тегів, ні проектів
	} else {
		return " AND 1=0"; // Гарантовано порожній результат
	}

	// --- ДОДАЄМО ДОДАТКОВІ УМОВИ ---

	// Умова 3: Виключаємо пости самого користувача
	$author_clause = $wpdb->prepare( " AND {$wpdb->posts}.post_author != %d", $user_id );

	// Повертаємо оригінальний $where, до якого додані наші кастомні умови
	return $where . $custom_where . $author_clause;
}
add_filter( 'posts_where', 'modify_discussions_archive_sql_where', 10, 2 );

/**
 * Важливе доповнення!
 * Оскільки ми втручаємось в WHERE, ми можемо зламати стандартні JOIN-и,
 * які WP додає для tax_query та meta_query.
 * Щоб цього уникнути, ми "обдуримо" WP, передавши порожні параметри,
 * які змусять його не генерувати зайві JOIN-и.
 */
function prevent_extra_joins_for_discussions( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive('discussion') ) {
		return;
	}

	// Якщо користувач - не адмін, ми передаємо ці "пустушки",
	// щоб WP_Query не намагався сам побудувати JOIN-и для них,
	// оскільки ми вже все зробили вручну в 'posts_where'.
	if ( ! is_current_user_admin() ) {
		$query->set('tax_query', []);
		$query->set('meta_query', []);
	}
}
add_action( 'pre_get_posts', 'prevent_extra_joins_for_discussions' );