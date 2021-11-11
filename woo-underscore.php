<?php

namespace _woo;

/*
 *
 * @link              https://github.com/midweste/woo-underscore
 * @since             1.0.0
 * @package           woo-underscore
 *
 * @wordpress-plugin
 * Plugin Name:       Woo Underscore
 * Plugin URI:        https://github.com/midweste/woo-underscore
 * Description:       Woo Underscore is a plugin for woocommerce developers with helper libraries and additional functions.
 * Version:           1.0.0
 * Author:            Codecide
 * Author URI:        https://github.com/midweste
 * License:           GPL-2.0+
 * Requires PHP:      7.2
 */

if (!defined('WPINC')) {
    die;
}

define('WOOUNDERSCORE', dirname(__FILE__));

// load underscore library
call_user_func(function () {
    if (class_exists('woocommerce')) {
        require_once WOOUNDERSCORE . '/vendor/autoload.php';
        foreach (glob(WOOUNDERSCORE . '/src/*.php') as $autoload) {
            require_once $autoload;
        }
    }
});
