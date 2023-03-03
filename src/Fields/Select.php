<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Select {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';
        $id = isset($args['multiple']) && $args['multiple'] === true ? "{$args['id']}_" . Pomatio_Framework_Helper::generate_random_string() : $args['id'];

        if (!isset($args['options'])) {
            return;
        }

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $id . '">' . $args['label'] . '</label><br>';
        }

        $multiple = isset($args['multiple']) && $args['multiple'] === true ? ' multiple' : '';
        $name = isset($args['multiple']) && $args['multiple'] === true ? "{$args['name']}[]" : $args['name'];

        echo '<select id="' . $id . '" name="' . $name . '" class="pomatio-framework-select form-control ' . $args['class'] . $multiple . '" data-type="select"' . $disabled . $multiple . '>';

        foreach ($args['options'] as $select_value => $select_label) {
            // optgroup options
            if (is_array($select_label)) {
                $label = isset($select_label['group_name']) ? ' label="' . $select_label['group_name'] . '"' : '';
                echo '<optgroup' . $label . '>';
                foreach ($select_label['group_options'] as $optgroup_key => $optgroup_label) {
                    $selected = '';
                    if (isset($args['value']) && $args['value'] === (string)$optgroup_key) {
                        $selected = 'selected';
                    }
                    elseif (isset($args['default']) && $args['default'] === (string)$optgroup_key) {
                        $selected = 'checked="checked"';
                    }

                    echo '<option value="' . $optgroup_key . '" ' . $selected . '>' . $optgroup_label . '</option>';
                }
                echo '</optgroup>';

                continue;
            }

            if (!empty($multiple)) {
                $values = explode(',', $args['value']);
                $selected = in_array($select_value, $values) ? 'selected' : '';
            }
            else {
                $selected = '';
                if (isset($args['value']) && !empty($args['value'])) {
                    $selected = selected($select_value, $args['value'], false);
                }
                elseif (isset($args['default']) && !empty($args['default'])) {
                    $selected = selected($select_value, $args['default'], false);
                }
            }

            echo '<option value="' . $select_value . '" ' . $selected . '>' . $select_label . '</option>';
        }

        echo '</select>';

        if (!empty($args['description'])) {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_script('pomatio-framework-select', POM_FORM_SRC_URI . '/dist/js/select' . POMATIO_MIN . '.js', ['jquery'], null, true);
    }

}
