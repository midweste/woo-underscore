<?php

namespace _woo;

use WC_Product;

function product_tag_id_from_slug( string $slug ) {
	$term = \get_term_by( 'slug', $slug, 'product_tag' );
	return $term->term_id;
}
