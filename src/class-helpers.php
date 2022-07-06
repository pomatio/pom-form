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
        /**
         * Libraries are added as associative arrays.
         * The primary key has to match the name of the
         * folder that contains the icons.
         * Then the array contains the name and the path.
         */
        $libraries['all'] = [
            'name' => __('All', 'pom-form'),
            'path' => ''
        ];

        return apply_filters('pom_form_icon_libraries', $libraries);
    }

    public static function get_color_palette(): array {
        return apply_filters('pom_form_color_palette', []);
    }

    /**
     * https://stackoverflow.com/questions/4356289/php-random-string-generator
     * @param int $length
     * @param bool $numbers
     * @return string
     */
    public static function generate_random_string(int $length = 10, bool $numbers = true): string {
        $number_string = $numbers ? '0123456789' : '';
        $characters = $number_string . 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function write_log($log): void {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

    public static function get_allowed_html(): array {
        $allowed_html = [
            'h1' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'h2' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'h3' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'h4' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'h5' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'h6' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'hr' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'form' => [
                'id' => true,
                'class' => true,
                'style' => true,
                'action' => true,
                'method' => true
            ],
            'iframe' => [
                'id' => true,
                'class' => true,
                'style' => true,
                'src' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'allowfullscreen' => true,
                'aria-hidden' => true,
                'tabindex' => true
            ],
            'button' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'blockquote' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'article' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'section' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'div' => [
                'id' => true,
                'class' => true,
                'style' => true,
            ],
            'span' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'a' => [
                'href' => true,
                'title' => true,
                'target' => true,
                'rel' => true,
                'class' => true
            ],
            'i' => true,
            'u' => true,
            'ol' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'ul' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'li' => [
                'id' => true,
                'class' => true,
                'style' => true,
            ],
            'img' => [
                'id' => true,
                'class' => true,
                'title' => true,
                'src' => true,
                'alt' => true,
                'width' => true,
                'height' => true
            ],
            'b' => true,
            'br' => true,
            'em' => true,
            's' => true,
            'strong' => [
                'class' => true
            ],
            'select' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'option' => [
                'class' => true
            ],
            'small' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'p' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'label' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'input' => [
                'id' => true,
                'class' => true,
                'style' => true,
                'type' => true,
                'name' => true,
                'value' => true,
                'placeholder' => true
            ],
            'ins' => [
                'class' => true,
                'data-*' => true
            ],
            'table' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'thead' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'tbody' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'tfoot' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'th' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'tr' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'td' => [
                'id' => true,
                'class' => true,
                'style' => true
            ],
            'style' => true,
            'del' => true,
            'map' => [
                'id' => true,
                'class' => true,
                'style' => true,
                'name' => true
            ],
            'area' => [
                'coords' => true,
                'shape' => true,
                'href' => true
            ],
            'pre' => true
        ];

        return apply_filters('pom_form_allowed_html', $allowed_html);
    }

}
