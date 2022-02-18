<?php

namespace _woo;

use WC_Product;

function product_parent_id( int $productId ): int {
	$product  = \wc_get_product( $productId );
	$parentId = $product->get_parent_id();
	return ( $parentId == 0 ) ? $productId : $parentId;
}

function product_permalink( string $productId ): string {
	$cacheName = \_\cache_name_create( 'post-' . $productId );
	if ( \_\cache_has( $cacheName ) ) {
		return \_\cache_get( $cacheName );
	}

	$entity = \wc_get_product( $productId );
	if ( ! $entity instanceof \WC_Product ) {
		return '';
	}
	$permalink = \_\permalink( $entity->get_id() );
	return \_\cache_set( $cacheName, $permalink );
}

function product_permalink_path( string $productId ): string {
	return \_\uri_relative( product_permalink( $productId ) );
}

function product_categories( WC_Product $product ): array {
	$terms = \wp_get_post_terms( $product->get_id(), 'product_cat' );
	return ( ! is_array( $terms ) ) ? [] : $terms;
}

function product_tags( WC_Product $product ): array {
	$terms = \wp_get_post_terms( $product->get_id(), 'product_tag' );
	return ( ! is_array( $terms ) ) ? [] : $terms;
}

function product_has_tag( WC_Product $product, string $tag ): bool {
	$terms = product_tags( $product );
	if ( empty( $terms ) ) {
		return false;
	}
	foreach ( $terms as $term ) {
		if ( ! empty( $term->name ) && $term->name == $tag ) {
			return true;
		}
	}
	return false;
}

function product_has_category( WC_Product $product, string $category ): bool {
	$terms = product_categories( $product );
	if ( empty( $terms ) ) {
		return false;
	}
	foreach ( $terms as $term ) {
		if ( ! empty( $term->name ) && $term->name == $category ) {
			return true;
		}
	}
	return false;
}
