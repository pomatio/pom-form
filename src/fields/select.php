<?php

namespace POM\Form;

class Select {

    public static function render_field(array $args): void {

        if (!isset($args['options'])) {
            return;
        }

        echo '<div class="form-group">';

            if (!empty($args['label'])) {
                echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
            }

            echo '<select id="' . $args['id'] . '" name="' . $args['name'] . '" class="form-control ' . $args['class'] . '">';

                foreach ($args['options'] as $select_value => $select_label) {

                    // optgroup options
                    if (is_array($select_label)) {
                        $label = isset($select_label['group_name']) ? ' label="' . $select_label['group_name'] . '"' : '';
                        echo '<optgroup' . $label . '>';
                        foreach ($select_label['group_options'] as $optgroup_key => $optgroup_label) {
                            $selected = $args['value'] === $optgroup_key ? 'selected' : '';
                            echo '<option value="' . $optgroup_key . '" ' . $selected . '>' . $optgroup_label . '</option>';
                        }
                        echo '</optgroup>';

                        continue;
                    }

                    // default option
                    $selected = $args['value'] === $select_value ? 'selected' : '';
                    echo '<option value="' . $select_value . '" ' . $selected . '>' . $select_label . '</option>';
                }

            echo '</select>';

            if (!empty($args['description'])) {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

        echo '</div>';

    }

}
