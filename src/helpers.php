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

    public static function get_uri(): string {
        return self::is_plugin() ? plugin_dir_url(__FILE__) : get_template_directory_uri();
    }

}
