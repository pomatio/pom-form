<?php

namespace POM\Form;

class Url {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        echo '<div class="form-group">';

            if (!empty($args['label'])) {
                echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
            }

            if (!empty($args['description']) && $args['description_position'] === 'below_label') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

            // Repeater integration
            $used_for_title = !empty($args['used_for_title']) ? ' use-for-title' : '';

            ?>

            <input aria-label="<?= $args['label'] ?? '' ?>" type="url" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-control<?= $used_for_title ?> <?= $args['class'] ?? '' ?>" data-type="url"<?= $disabled ?>>

            <?php

            if (!empty($args['description']) && $args['description_position'] === 'under_field') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

        echo '</div>';
    }

}
