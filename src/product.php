<?php

namespace _woo;

use WC_Product;

function product_parent_id(int $productId): int
{
    $product  = \wc_get_product($productId);
    $parentId = $product->get_parent_id();
    return ($parentId == 0) ? $productId : $parentId;
}

function product_permalink(string $productId): string
{
    $cacheName = \_\cache_name_create('post-' . $productId);
    if (\_\cache_has($cacheName)) {
        return \_\cache_get($cacheName);
    }

    $entity = \wc_get_product($productId);
    if (!$entity instanceof \WC_Product) {
        return '';
    }
    $permalink = \_\permalink($entity->get_id());
    return \_\cache_set($cacheName, $permalink);
}

function product_permalink_path(string $productId): string
{
    return \_\uri_relative(product_permalink($productId));
}

function product_categories(WC_Product $product): array
{
    $terms = \wp_get_post_terms($product->get_id(), 'product_cat');
    return (!is_array($terms)) ? [] : $terms;
}

function product_tags(WC_Product $product): array
{
    $terms = \wp_get_post_terms($product->get_id(), 'product_tag');
    return (!is_array($terms)) ? [] : $terms;
}

function product_has_tag(WC_Product $product, string $tag): bool
{
    $terms = product_tags($product);
    if (empty($terms)) {
        return false;
    }
    foreach ($terms as $term) {
        if (!empty($term->name) && $term->name == $tag) {
            return true;
        }
    }
    return false;
}

function product_has_category(WC_Product $product, string $category): bool
{
    $terms = product_categories($product);
    if (empty($terms)) {
        return false;
    }
    foreach ($terms as $term) {
        if (!empty($term->name) && $term->name == $category) {
            return true;
        }
    }
    return false;
}

function product_search_replace_attributes(\WC_Product $product, $search, $replace): \WC_Product
{
    if (empty($search)) {
        return $product;
    }

    /** @var WC_Product_Attribute[] $attributes */
    $attributes = $product->get_attributes('edit');
    if (empty($attributes)) {
        // $this->log(LogLevel::INFO, sprintf('Product %d does not have attributes', $pid));
        return $product;
    }

    // need to clone the array of attributes otherwise the underlying data is changed and woo doesnt detect any changes
    $cloned = [];
    foreach ($attributes as $index => $attribute) {
        $cloned[$index] = clone $attribute;
    }

    $modified = false;
    foreach ($cloned as $attribute) {
        /** @var WC_Product_Attribute $attribute */
        if ($attribute->is_taxonomy()) {
            continue;
        }

        $options = $attribute->get_options('edit');
        foreach ($options as &$option) {
            if (!is_string($option)) {
                continue;
            }

            $option = (mb_detect_encoding($option) !== 'ASCII') ? htmlentities($option) : $option;
            $replaced = str_replace($search, $replace, $option);
            if ($option !== $replaced) {
                $option = $replaced;
                $modified = true;
            }
        }
        if ($modified) {
            $attribute->set_options($options);
        }
    }
    if ($modified) {
        $product->set_attributes($cloned);
        return  $product;
    }
    return  $product;
}

function product_search_replace_field(string $field_name, \WC_Product $product, $search, $replace): \WC_Product
{
    if (empty($search)) {
        return $product;
    }

    if (!method_exists($product, 'get_' . $field_name) || !method_exists($product, 'set_' . $field_name)) {
        throw new \Exception(sprintf('Method does not exist'));
    }

    $field_value = $product->{'get_' . $field_name}('edit');
    if (empty($field_value) || !is_string($field_value)) {
        //$this->log(LogLevel::INFO, sprintf('Product %d does not have %s', $pid, $field_name));
        return $product;
    }

    $field_value = (mb_detect_encoding($field_value) !== 'ASCII') ? htmlentities($field_value) : $field_value;
    $replaced = str_replace($search, $replace, $field_value);
    if ($field_value !== $replaced) {
        $product->{'set_' . $field_name}($replaced);
        return $product;
    }
    return $product;
}
