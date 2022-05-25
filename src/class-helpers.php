<?php

namespace POM\Form;

class POM_Form_Helper {

    /**
     * This function will return true if the current file
     * is loaded in a plugin and false if it is loaded in a theme.
     *
     * https://wordpress.stackexchange.com/a/213044
     *
     * @return bool
     */
    public static function is_plugin(): bool {
        return strpos( str_replace("\\", "/", plugin_dir_path( __FILE__ ) ) , str_replace("\\", "/", WP_PLUGIN_DIR) ) !== false;
    }

    public static function get_path(): string {
        return self::is_plugin() ? plugin_dir_path(__FILE__) : get_template_directory();
    }

    public static function get_uri($file = ''): string {
        $file = !empty($file) ? $file : __FILE__;
        return self::is_plugin() ? plugin_dir_url($file) : get_template_directory_uri();
    }

    /**
     * Convert an associative array into valid HTML attributes
     */
    public static function convert_array_to_html_attributes($field_args): string {
        return implode(' ', array_map(static function($key) use ($field_args) {
            if (is_bool($field_args[$key])) {
                return $field_args[$key] ? $key : '';
            }

            return $key . '="' . $field_args[$key] . '"';
        }, array_keys($field_args)));
    }

    public static function convert_html_attributes_to_array($string) : array {
        $array = [];

        $asArr = explode(' ', $string);
        foreach ($asArr as $val) {
            $tmp = explode('=', str_replace(['"', "'"], '', $val));
            $array[$tmp[0]] = $tmp[1];
        }

        return $array;
    }

    public static function path_to_url($path = ''): string {
        $url = str_replace(
            wp_normalize_path(untrailingslashit(ABSPATH)),
            site_url(),
            wp_normalize_path($path)
        );

        return esc_url_raw($url);
    }

    public static function get_icon_libraries(): array {
        return apply_filters('pom_form_icon_libraries', []);
    }

    public static function get_color_palette(): array {
        return apply_filters('pom_form_color_palette', []);
    }

}
