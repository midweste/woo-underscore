<?php

namespace _woo;

function template_wc_get_template_filter_add(string $template_dir): void
{
    if (!is_dir($template_dir)) {
        throw new \Exception(sprintf('Template directory %s does not exist', $template_dir));
    }
    add_filter('wc_get_template', function ($template, $template_name, $args, $template_path, $default_path) use ($template_dir) {
        /* custom theme templates has priority */
        if (strpos($template, '/themes/') !== false) {
            return $template;
        }

        static $cache = array();
        if (isset($cache[$template_name])) {
            return $cache[$template_name];
        }

        $plugin_template = wc_locate_template($template_name, \WC()->template_path(), $template_dir);
        if ($plugin_template && file_exists($plugin_template)) {
            $template = $plugin_template;
            $cache[$template_name] = $template;
        }
        return $template;
    }, 20, 5);
}

function template_wc_get_template_part_filter_add(string $template_dir): void
{
    if (!is_dir($template_dir)) {
        throw new \Exception(sprintf('Template directory %s does not exist', $template_dir));
    }
    add_filter('wc_get_template_part', function ($template, $slug, $name) use ($template_dir) {
        /* custom theme templates has priority */
        if (strpos($template, '/themes/') !== false) {
            return $template;
        }

        $template_name = '';
        if ($name) {
            $template_name = "{$slug}-{$name}.php";
        } elseif ($slug) {
            $template_name = "{$slug}.php";
        }
        if (!$template_name) {
            return $template;
        }

        static $cache = array();
        if (isset($cache[$template_name])) {
            return $cache[$template_name];
        }

        $plugin_template = $template_dir . '/' . $template_name;
        if ($plugin_template && file_exists($plugin_template)) {
            $template = $plugin_template;
            $cache[$template_name] = $template;
        }

        return $template;
    }, 20, 3);
}
