<?php

namespace POM\Form;

const POM_FORM_VERSION = '0.1.0';

class Form {

    public function __construct() {
        /**
         * Dynamically includes all the files that are inside the directory
         */
        foreach (glob(POM_FORM_PATH . 'fields/*.php') as $filename) {
            include_once $filename;
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
            'name' => sanitize_title($field_args['name']),
            'id' => $field_args['id'] ?? sanitize_title($field_args['name']),
            'value' => $field_args['value'] ?? '',
            'class' => $field_args['class'] ?? '',
            'description_position' => $field_args['description_position'] ?? 'under_field',
            'options' => $field_args['options'] ?? [],
        ];

        foreach ($required_attrs as $key => $value) {
            unset($field_args[$key]);
        }

        /**
         * Convert the remaining values of the associative array into valid HTML attributes
         */
        $custom_attrs = implode(' ', array_map(static function($key) use ($field_args) {
            if (is_bool($field_args[$key])) {
                return $field_args[$key] ? $key : '';
            }

            return $key . '="' . $field_args[$key] . '"';
        }, array_keys($field_args)));

        return array_merge($required_attrs, ['custom_attrs' => $custom_attrs]);
    }

    /**
     * Returns the HTML of the field.
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
