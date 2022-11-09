<?php

namespace PomatioFramework;

const POM_FORM_VERSION = '0.1.0';
define('POM_FORM_SRC_PATH', __DIR__);
define('POM_FORM_SRC_URI', str_replace($_SERVER['DOCUMENT_ROOT'], '', POM_FORM_SRC_PATH));

class Pomatio_Framework {

    public function __construct() {
        /**
         * Functions available throughout the framework.
         */
        require_once 'Pomatio_Framework_Helper.php';
        require_once 'class-sanitize.php';
        require_once 'Pomatio_Framework_Disk.php';
        require_once 'Pomatio_Framework_Settings.php';
        require_once 'Pomatio_Framework_Ajax.php';
        require_once 'Pomatio_Framework_Save.php';
    }

    /**
     * Analyze the values before rendering the field
     * and set default values if they are not set.
     *
     * Allow custom attributes. These are not analyzed.
     *
     * @param array $args
     *
     * @return array
     */
    private static function parse_args(array $args): array {
        $field_args = wp_parse_args($args);

        // Make sure the basic attributes have valid values.
        $field_args['type'] = $field_args['type'] ?? 'text';
        $field_args['label'] = $field_args['label'] ?? '';
        $field_args['description'] = $field_args['description'] ?? '';
        $field_args['name'] = sanitize_title($field_args['name']);
        $field_args['id'] = $field_args['id'] ?? sanitize_title($field_args['name']);
        $field_args['value'] = $field_args['value'] ?? '';
        $field_args['class'] = isset($field_args['class']) ? sanitize_html_class($field_args['class']) : '';
        $field_args['description_position'] = $field_args['description_position'] ?? 'under_field';
        $field_args['options'] = $field_args['options'] ?? [];
        $field_args['prefix'] = $field_args['prefix'] ?? '';
        $field_args['suffix'] = $field_args['suffix'] ?? '';
        $field_args['default'] = $field_args['default'] ?? '';

        return $field_args;
    }

    /**
     * Returns the HTML of the field.
     *
     * @param array $args
     *
     * @return string
     */
    public static function add_field(array $args): string {
        $type = ucfirst($args['type']);

        if (!file_exists($filename = Pomatio_Framework_Helper::get_path() . "/Fields/$type.php")) {
            return '';
        }

        include_once $filename;

        $class = "PomatioFramework\\Fields\\$type";
        $field_class = new $class();

        $field_args = self::parse_args($args);

        ob_start();

        $field_class::render_field($field_args);

        /**
         * If the field is of type repeater and has a code editor inside,
         * it is necessary to do the enqueue from the beginning.
         * Otherwise, the field is not rendered.
         */
        if ($field_args['type'] === 'repeater' && !empty($field_args['fields'])) {
            foreach ($field_args['fields'] as $repeater_field) {
                if (isset($repeater_field['type']) && ($repeater_field['type'] === 'code_html' || $repeater_field['type'] === 'code_css' || $repeater_field['type'] === 'code_js')) {
                    $codemirror_settings = wp_enqueue_code_editor([]);
                    wp_enqueue_script('wp-theme-plugin-editor');
                    wp_enqueue_style('wp-codemirror');
                    wp_enqueue_script('pomatio-framework-code', POM_FORM_SRC_URI . '/dist/js/code.min.js', ['jquery', 'wp-theme-plugin-editor'], NULL, true);
                    wp_localize_script(
                        'pomatio-framework-code',
                        'settings',
                        [
                            'codeMirrorSettings' => $codemirror_settings
                        ]
                    );
                }

                if (isset($repeater_field['type']) && $repeater_field['type'] === 'Color') {
                    wp_enqueue_style('wp-color-picker');
                    wp_enqueue_script('pomatio-framework-color', POM_FORM_SRC_URI . '/dist/js/color.min.js', ['wp-color-picker'], null, true);
                }

                if (isset($repeater_field['type']) && $repeater_field['type'] === 'Font_Picker') {
                    wp_enqueue_style('pomatio-framework-font_picker', POM_FORM_SRC_URI . '/dist/css/font-picker.min.css');
                    wp_enqueue_script('pomatio-framework-font_picker',  POM_FORM_SRC_URI . '/dist/js/font_picker.min.js', ['jquery'], null, true);
                    wp_localize_script(
                        'pomatio-framework-font_picker',
                        'pom_form_font_picker',
                        [
                            'title' => __('Choose Font', 'pomatio-framework'),
                            'button' => __('Choose Font', 'pomatio-framework'),
                        ]
                    );
                }
            }
        }

        return ob_get_clean();
    }

}

new Pomatio_Framework();
