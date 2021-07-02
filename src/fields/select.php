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

            echo '<select id="' . $args['id'] . '" class="form-control ' . $args['class'] . '">';

                foreach ($args['options'] as $select_value => $select_label) {
                    echo '<option value="' . $select_value . '">' . $select_label . '</option>';
                }

            echo '</select>';

            if (!empty($args['description'])) {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

        echo '</div>';

    }

}
