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
