<?php

add_filter('carbon_fields_association_field_title', 'bsn_8723_article_titles', 10, 5);

function bsn_8723_article_titles($title, $name, $id, $type, $subtype)
{
    if (get_post_type($id) == 'products') {
        $cat = get_the_terms($id, 'categoryes')[0];
        $cat = get_term_top_most_parent($cat->term_id, 'categoryes');
        if ($cat) {
            $title .= ' - Категория ' . $cat->name;

        }
    }
    return $title;
}