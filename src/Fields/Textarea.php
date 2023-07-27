<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Textarea {

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
            if (!empty($args['value'])) {
                $value = $args['value'];
            }
            elseif (!empty($args['default'])) {
                $value = $args['default'];
            }

            $class = !empty($args['class']) ? ' ' . $args['class'] : '';

            ?>

            <textarea aria-label="<?= $args['label'] ?? '' ?>" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" class="form-control<?= $class ?>"  data-type="textarea"<?= $disabled ?><?= $data_dependencies ?>><?= $value ?></textarea>

            <?php

            if (!empty($args['description']) && $args['description_position'] === 'under_field') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

        echo '</div>';
    }

}
