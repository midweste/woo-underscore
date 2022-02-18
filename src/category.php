<?php

namespace _woo;

use WC_Product;

function product_category_id_from_slug( string $slug ) {
	$term = \get_term_by( 'slug', $slug, 'product_cat' );
	return $term->term_id;
}
