<?php

namespace POM\Form;

class Quantity {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        ?>

        <div class="quantity">
            <span class="number-down"></span>
            <input aria-label="<?= $args['label'] ?? '' ?>" type="number" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-control input-text qty text <?= $args['class'] ?? '' ?>" pattern="[0-9]*" inputmode="numeric" aria-labelledby="" data-type="quantity">
            <span class="number-up"></span>
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

    }

}
