<?php

namespace PomatioFramework\Fields;

class Email {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        echo '<div class="form-group">';

            if (!empty($args['label'])) {
                echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
            }

            if (!empty($args['description']) && $args['description_position'] === 'below_label') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

            $value = '';
            if (isset($args['value']) && !empty($args['value'])) {
                $value = $args['value'];
            }
            elseif (isset($args['default']) && !empty($args['default'])) {
                $value = $args['default'];
            }

            ?>

            <input aria-label="<?= $args['label'] ?? '' ?>" type="email" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" class="form-control <?= $args['class'] ?? '' ?>" data-type="email"<?= $disabled ?>>

            <?php

            if (!empty($args['description']) && $args['description_position'] === 'under_field') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

        echo '</div>';
    }

}
