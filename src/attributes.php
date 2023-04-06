<?php

namespace _woo;

/**
 * Return an array of all attributes
 */
function attributes(): array
{
    $attributes = wc_get_attribute_taxonomies();
    $attribute_array = array();
    foreach ($attributes as $attribute) {
        $attribute_array[$attribute->attribute_name] = $attribute;
    }
    return $attribute_array;
}

/**
 * Return an array of terms for a given attribute
 *
 * @param string $attribute_name
 * @return array
 */
function attribute_terms(string $attribute_name): array
{
    $taxonomy = wc_attribute_taxonomy_name($attribute_name);
    $terms = get_terms($taxonomy, array(
        'hide_empty' => false,
    ));
    if ($terms instanceof \WP_Error) {
        return [];
    }
    $term_objects = [];
    foreach ($terms as $term) {
        $term_objects[$term->slug] = $term;
    }
    return $term_objects;
}

/**
 * Return an array of attributes and terms
 *
 * @return array
 */
function attributes_terms(): array
{
    $attributes = attributes();
    $attributes_and_terms =  [];
    foreach ($attributes as $attribute) {
        $terms = attribute_terms($attribute->attribute_name);
        $attributes_and_terms[$attribute->attribute_name] = $terms;
    }
    return $attributes_and_terms;
}
