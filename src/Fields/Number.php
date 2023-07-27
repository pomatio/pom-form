<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Number {

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

        $value = '';
        if (isset($args['value']) && is_numeric($args['value'])) {
            $value = $args['value'];
        }
        elseif (isset($args['default']) && is_numeric($args['default'])) {
            $value = $args['default'];
        }

        ?>

        <input aria-label="<?= $args['label'] ?? '' ?>" placeholder="<?= $args['placeholder'] ?? '' ?>" type="number" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" class="form-control <?= $args['class'] ?? '' ?>"<?= $data_dependencies ?> data-type="number"<?= $disabled ?>>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';
    }

}
