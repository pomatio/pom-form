<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Trbl {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';
        $data_dependencies = Pomatio_Framework_Helper::get_dependencies_data_attr($args);

        $units = [];
        if (!empty($args['units']) && is_array($args['units'])) {
            foreach ($args['units'] as $unit) {
                if (is_string($unit) && $unit !== '') {
                    $units[] = sanitize_text_field($unit);
                }
            }
        }
        if (empty($units)) {
            $units = ['px'];
        }

        $default_unit = $units[0];
        $sides = [
            'top' => __('Top', 'pomatio-framework'),
            'right' => __('Right', 'pomatio-framework'),
            'bottom' => __('Bottom', 'pomatio-framework'),
            'left' => __('Left', 'pomatio-framework'),
        ];

        $value = is_array($args['value']) ? $args['value'] : [];
        $default = is_array($args['default']) ? $args['default'] : [];

        $sync_enabled = !isset($args['sync']) || $args['sync'] !== false;
        $sync_active = $sync_enabled && ((isset($value['sync']) && $value['sync'] === 'yes') || (!isset($value['sync']) && (!empty($default['sync']) ? $default['sync'] === 'yes' : true)));

        if (!$sync_enabled) {
            $sync_active = false;
        }

        $wrapper_classes = ['form-group', 'pomatio-trbl'];
        if ($sync_active) {
            $wrapper_classes[] = 'is-locked';
        }

        echo '<div class="' . implode(' ', $wrapper_classes) . '"' . $data_dependencies . '>';

        if (!empty($args['label']) || $sync_enabled) {
            echo '<div class="pomatio-trbl__header">';

            if (!empty($args['label'])) {
                echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
            }

            if ($sync_enabled) {
                $sync_label = $sync_active ? __('Values are locked', 'pomatio-framework') : __('Values are independent', 'pomatio-framework');
                $dashicon = $sync_active ? 'dashicons-lock' : 'dashicons-unlock';

                echo '<button type="button" class="button pomatio-trbl__sync ' . ($sync_active ? 'is-locked' : 'is-unlocked') . '" aria-pressed="' . ($sync_active ? 'true' : 'false') . '" aria-label="' . esc_attr($sync_label) . '">';
                echo '<span class="dashicons ' . $dashicon . '"></span>';
                echo '</button>';
            }

            echo '</div>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '<input type="hidden" class="pomatio-trbl__sync-state" name="' . $args['name'] . '[sync]" value="' . ($sync_active ? 'yes' : 'no') . '">';

        echo '<div class="pomatio-trbl__grid">';

        foreach ($sides as $side_key => $side_label) {
            $side_value = '';
            $side_unit = $default_unit;

            if (isset($value[$side_key])) {
                if (is_array($value[$side_key])) {
                    $side_value = $value[$side_key]['value'] ?? '';
                    $side_unit = $value[$side_key]['unit'] ?? $default_unit;
                }
                elseif (is_scalar($value[$side_key])) {
                    $side_value = $value[$side_key];
                }
            }
            elseif (isset($default[$side_key])) {
                if (is_array($default[$side_key])) {
                    $side_value = $default[$side_key]['value'] ?? '';
                    $side_unit = $default[$side_key]['unit'] ?? $default_unit;
                }
                elseif (is_scalar($default[$side_key])) {
                    $side_value = $default[$side_key];
                }
            }

            echo '<div class="pomatio-trbl__field pomatio-trbl__field--' . $side_key . '">';
            echo '<div class="pomatio-trbl__field-label">' . $side_label . '</div>';
            echo '<div class="pomatio-trbl__field-controls">';
            echo '<input aria-label="' . esc_attr($side_label) . '" type="number" name="' . $args['name'] . '[' . $side_key . '][value]" value="' . esc_attr($side_value) . '" class="form-control pomatio-trbl__value ' . ($args['class'] ?? '') . '" data-side="' . $side_key . '" data-type="trbl"' . $disabled . '>';

            if (count($units) > 1) {
                echo '<select class="pomatio-trbl__unit-select" name="' . $args['name'] . '[' . $side_key . '][unit]"' . $disabled . '>';
                foreach ($units as $unit) {
                    echo '<option value="' . esc_attr($unit) . '"' . selected($side_unit, $unit, false) . '>' . esc_html($unit) . '</option>';
                }
                echo '</select>';
            }
            else {
                echo '<input type="hidden" name="' . $args['name'] . '[' . $side_key . '][unit]" value="' . esc_attr($side_unit) . '">';
                echo '<span class="pomatio-trbl__unit-badge">' . esc_html($side_unit) . '</span>';
            }

            echo '</div>';
            echo '</div>';
        }

        echo '</div>';

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pomatio-framework-trbl', POM_FORM_SRC_URI . '/dist/css/trbl' . POMATIO_MIN . '.css');
        wp_enqueue_script('pomatio-framework-trbl', POM_FORM_SRC_URI . '/dist/js/trbl' . POMATIO_MIN . '.js', ['jquery'], null, true);
    }

}