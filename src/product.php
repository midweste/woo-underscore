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
            $replaced = trim(str_ireplace($search, $replace, $option));
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
    $replaced = trim(str_ireplace($search, $replace, $field_value));
    if ($field_value !== $replaced) {
        $product->{'set_' . $field_name}($replaced);
        return $product;
    }
    return $product;
}

function product_ids(): array
{
    $ids = get_posts(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'fields' => 'ids',
        'order_by' => 'id',
        'order' => 'ASC',
    ));
    return (!empty($ids)) ? $ids : [];
}

function product_ids_published(): array
{
    $ids = get_posts(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
        'order_by' => 'id',
        'order' => 'ASC',
    ));
    return (!empty($ids)) ? $ids : [];
}

function product_ids_published_visible(array $args = []): array
{
    $args = array_merge_recursive(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_visibility',
                'field' => 'slug',
                'terms' => ['exclude-from-catalog', 'exclude-from-search'],
                'operator' => 'NOT IN'
            )
        )
    ), $args);

    $ids =  get_posts($args);
    return (!empty($ids)) ? $ids : [];
}

/**
 * Return an array of product ids for a given attribute and term
 *
 * @param string $attribute_name
 * @param integer $term_id
 * @return array
 */
function product_ids_by_attribute_term_id(string $attribute_name, int $term_id): array
{
    $args = array(
        'limit' => -1,
        'return' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => wc_attribute_taxonomy_name($attribute_name),
                'field' => 'term_id',
                'terms' => $term_id,
                'include_children' => false
            ]
        ]
    );
    $products = wc_get_products($args);
    return $products;
}

/**
 * Return html based on template for an array of products
 *
 * @param array $ids
 * @return string html
 */
function product_loop(array $products, string $template_slug = 'content', string $template_name = 'product'): string
{
    if (empty($products)) {
        return '';
    }

    foreach ($products as &$product) {
        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }
    }

    $query = new \WP_Query();
    $query->posts = $products;
    $query->post_count = count($products);

    ob_start();
    woocommerce_product_loop_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            do_action('woocommerce_shop_loop');
            wc_get_template_part($template_slug, $template_name);
        }
    } else {
        do_action('woocommerce_no_products_found');
    }
    woocommerce_product_loop_end();
    $output = ob_get_clean();

    wp_reset_postdata();
    return $output;
}

/**
 * Return html for an array of products
 *
 * @param array $products
 * @return string html
 */
function product_cards(array $products)
{
    if (empty($products)) {
        return '';
    }
    $html = "<div class='woocommerce'><div class='row loop'><div class='col'>";
    $html .= product_loop($products);
    $html .= '</div></div></div>';
    return $html;
}
