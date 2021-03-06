<?php

namespace POM\Form;

const POM_FORM_VERSION = '0.1.0';
define('POM_FORM_SRC_PATH', __DIR__);
define('POM_FORM_SRC_URI', str_replace($_SERVER['DOCUMENT_ROOT'], '', POM_FORM_SRC_PATH));

class Form {

    public function __construct() {
        require 'vendor/autoload.php';

        /**
         * Functions available throughout the framework.
         */
        require_once 'class-helpers.php';
        require_once 'class-sanitize.php';
        require_once 'class-disk.php';
        require_once 'class-settings.php';
        require_once 'class-ajax.php';
        require_once 'class-save.php';
    }

    /**
     * Analyze the values before rendering the field
     * and set default values if they are not set.
     *
     * Allow custom attributes. These are not analyzed.
     *
     * @param array $args
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
        $field_args['class'] = isset($field_args['class']) ? sanitize_html_class($field_args['class']) :  '';
        $field_args['description_position'] = $field_args['description_position'] ?? 'under_field';
        $field_args['options'] = $field_args['options'] ?? [];
        $field_args['prefix'] = $field_args['prefix'] ?? '';
        $field_args['suffix'] = $field_args['suffix'] ?? '';

        return $field_args;
    }

    /**
     * Returns the HTML of the field.
     *
     * @param array $args
     * @return string
     */
    public static function add_field(array $args): string {
        $type = strtolower($args['type']);

        if (!file_exists($filename = POM_Form_Helper::get_path() . "fields/$type.php")) {
            return '';
        }

        include_once $filename;

        $class = 'POM\Form\\' . ucfirst($args['type']);
        $field_class = new $class();

        $field_args = self::parse_args($args);

        ob_start();

        $field_class::render_field($field_args);

        /**
         * If the field is of type repeater and has a code editor inside,
         * it is necessary to do the enqueue from the beginning.
         * Otherwise, the field is not rendered.
         */
        if ($field_args['type'] === 'repeater') {
            foreach ($field_args['fields'] as $repeater_field) {
                if (isset($repeater_field['type']) && ($repeater_field['type'] === 'code_html' || $repeater_field['type'] === 'code_css' || $repeater_field['type'] === 'code_js')) {
                    $codemirror_settings = wp_enqueue_code_editor([]);
                    wp_enqueue_script('wp-theme-plugin-editor');
                    wp_enqueue_style('wp-codemirror');
                    wp_enqueue_script('pom-form-code', POM_FORM_SRC_URI . '/dist/js/code.min.js', ['jquery', 'wp-theme-plugin-editor'], NULL, true);
                    wp_localize_script(
                        'pom-form-code',
                        'settings',
                        [
                            'codeMirrorSettings' => $codemirror_settings
                        ]
                    );
                }
            }
        }

        return ob_get_clean();
    }

}
new Form();
