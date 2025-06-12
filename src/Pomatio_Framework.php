<?php

namespace PomatioFramework;

const POM_FORM_VERSION = '0.1.0';

define('POMATIO_MIN', defined('WP_DEBUG') && WP_DEBUG !== true ? '.min' : '');
define('POM_FORM_SRC_PATH', __DIR__);
define('POM_FORM_SRC_URI', str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(POM_FORM_SRC_PATH)));

class Pomatio_Framework {

    public function __construct() {
        require_once 'class-sanitize.php';
        require_once 'cli/init-cli.php';

        $classes = [
            Pomatio_Framework_Helper::class => 'Pomatio_Framework_Helper.php',
            Pomatio_Framework_Disk::class => 'Pomatio_Framework_Disk.php',
            Pomatio_Framework_Settings::class => 'Pomatio_Framework_Settings.php',
            Pomatio_Framework_Ajax::class => 'Pomatio_Framework_Ajax.php',
            Pomatio_Framework_Save::class => 'Pomatio_Framework_Save.php',
            Pomatio_Framework_Translations::class => 'Pomatio_Framework_Translations.php',
        ];

        foreach ($classes as $class => $file) {
            if (!class_exists($class)) {
                require_once POM_FORM_SRC_PATH . '/' . $file;
            }
        }

        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts($hook): void {
        if ($hook === 'settings_page_pom-theme-options') {
            wp_enqueue_style('pomatio-framework-settings', POM_FORM_SRC_URI . '/dist/css/admin.min.css');
        }
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
        $field_args['name'] = isset($field_args['name']) ? sanitize_title($field_args['name']) : '';
        $field_args['id'] = $field_args['id'] ?? $field_args['name'];
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
                if (isset($repeater_field['type']) && ($repeater_field['type'] === 'code_html' || $repeater_field['type'] === 'code_css' || $repeater_field['type'] === 'code_js' || $repeater_field['type'] === 'code_json')) {
                    $codemirror_settings = wp_enqueue_code_editor([]);

                    wp_enqueue_script('wp-theme-plugin-editor');
                    wp_enqueue_style('wp-codemirror');
                    wp_enqueue_script('pomatio-framework-code', POM_FORM_SRC_URI . '/dist/js/code' . POMATIO_MIN . '.js', ['jquery', 'wp-theme-plugin-editor'], null, true);
                    wp_localize_script(
                        'pomatio-framework-code',
                        'settings',
                        [
                            'codeMirrorSettings' => $codemirror_settings
                        ]
                    );
                }

                if (isset($repeater_field['type']) && $repeater_field['type'] === 'Select') {
                    wp_enqueue_script('pomatio-framework-select', POM_FORM_SRC_URI . '/dist/js/select' . POMATIO_MIN . '.js', ['jquery'], null, true);
                }

                if (isset($repeater_field['type']) && $repeater_field['type'] === 'Color') {
                    wp_enqueue_style('wp-color-picker');
                    wp_enqueue_script('pomatio-framework-color', POM_FORM_SRC_URI . '/dist/js/color' . POMATIO_MIN . '.js', ['wp-color-picker'], null, true);
                }

                if (isset($repeater_field['type']) && $repeater_field['type'] === 'Font_Picker') {
                    wp_enqueue_style('pomatio-framework-font_picker', POM_FORM_SRC_URI . '/dist/css/font-picker.min.css');
                    wp_enqueue_script('pomatio-framework-font_picker', POM_FORM_SRC_URI . '/dist/js/font_picker' . POMATIO_MIN . '.js', ['jquery'], null, true);
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
