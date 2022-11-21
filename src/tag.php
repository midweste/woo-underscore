<?php

namespace _woo;

function product_tag_id_from_slug(string $slug)
{
    $term = \get_term_by('slug', $slug, 'product_tag');
    if (!$term instanceof \WP_Term || !is_array($term) || empty($term->term_id)) {
        return;
    }
    return $term->term_id;
}
