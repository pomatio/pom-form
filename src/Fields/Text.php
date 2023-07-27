<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Text {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';
        $data_dependencies = Pomatio_Framework_Helper::get_dependencies_data_attr($args);

        echo '<div class="form-group">';

            if (!empty($args['label'])) {
                echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label><br>';
            }

            if (!empty($args['description']) && $args['description_position'] === 'below_label') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

            // Repeater integration
            $used_for_title = !empty($args['used_for_title']) ? ' use-for-title' : '';

            $value = '';
            if (!empty($args['value'])) {
                $value = $args['value'];
            }
            elseif (!empty($args['default'])) {
                $value = $args['default'];
            }

            ?>

            <input aria-label="<?= $args['label'] ?? '' ?>" placeholder="<?= $args['placeholder'] ?? '' ?>" type="text" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" class="form-control<?= $used_for_title ?> <?= $args['class'] ?? '' ?>"<?= $data_dependencies ?> data-type="text"<?= $disabled ?>>

            <?php

            if (!empty($args['description']) && $args['description_position'] === 'under_field') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

        echo '</div>';
    }

}
