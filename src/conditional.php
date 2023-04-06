<?php

namespace _woo;

function is_order_admin_page()
{
    global $pagenow;
    if (
        empty($pagenow)
        || $pagenow !== 'edit.php'
        || empty($_GET['post_type'])
        || $_GET['post_type'] !== 'shop_order'
    ) {
        return false;
    }
    return true;
}

function is_attribute_admin_page()
{
    global $pagenow;
    if (
        empty($pagenow)
        || $pagenow !== 'edit-tags.php'
        || empty($_GET['post_type'])
        || $_GET['post_type'] !== 'product'
    ) {
        return false;
    }
    return true;
}

function is_page_tab_section(string $page, string $tab, string $section): bool
{
    $get = $_GET;
    $is_page = (isset($get['page']) && $get['page'] === $page) ? true : false;
    $is_tab = (isset($get['tab']) && $get['tab'] === $tab) ? true : false;
    $is_section = (isset($get['section']) && $get['section'] === $section) ? true : false;
    return ($is_page && $is_tab && $is_section) ? true : false;
}
