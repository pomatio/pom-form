<?php

namespace POM\Form;

define('POM_FORM_PATH', trailingslashit(__DIR__));

class Form {

    public function __construct() {
        foreach (glob(POM_FORM_PATH . "fields/*.php") as $filename) {
            include $filename;
        }
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
        $required_attrs = [
            'type' => $field_args['type'] ?? 'text',
            'label' => $field_args['label'] ?? '',
            'description' => $field_args['description'] ?? '',
            'name' => $field_args['name'],
            'id' => $field_args['id'] ?? $field_args['name']
        ];

        unset($field_args['type'], $field_args['label'], $field_args['description'], $field_args['name'], $field_args['id']);

        $custom_attrs = urldecode(str_replace("=", '="', http_build_query($field_args, null, '" '))) . '"';

        return array_merge($required_attrs, ['custom_attrs' => $custom_attrs]);
    }

    /**
     * Returns the HTML of the field
     *
     * @param array $args
     * @return string
     */
    public static function add_field(array $args): string {

        $class = '\POM\Form\\' . ucfirst($args['type']);

        if (!class_exists($class)) {
            return '';
        }

        $field_args = self::parse_args($args);

        ob_start();

        $field_class = new $class();
        $field_class::render_field($field_args);

        return ob_get_clean();

    }

}
