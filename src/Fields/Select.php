<?php

namespace PomatioFramework\Fields;

class Select {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        if (!isset($args['options'])) {
            return;
        }

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }
        $multiple = isset($args['multiple']) && $args['multiple'] === true ? ' multiple' : '';
        $name = isset($args['multiple']) && $args['multiple'] === true ? "{$args['name']}[]" : $args['name'];

        echo '<select id="' . $args['id'] . '" name="' . $name . '" class="pomatio-framework-select form-control ' . $args['class'] . $multiple . '" data-type="select"' . $disabled . $multiple . '>';

        foreach ($args['options'] as $select_value => $select_label) {
            // optgroup options
            if (is_array($select_label)) {
                $label = isset($select_label['group_name']) ? ' label="' . $select_label['group_name'] . '"' : '';
                echo '<optgroup' . $label . '>';
                foreach ($select_label['group_options'] as $optgroup_key => $optgroup_label) {
                    $selected = (string)$args['value'] === (string)$optgroup_key ? 'selected' : '';
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
                $selected = selected($select_value, $args['value'], false);
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
