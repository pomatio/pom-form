<?php

namespace PomatioFramework\fields;

class Radio {

    public static function render_field(array $args): void {

        if (!isset($args['options'])) {
            return;
        }

        if (!empty($args['label'])) {
            echo '<label>' . $args['label'] . '</label>';
        }

        foreach ($args['options'] as $radio_value => $radio_label) {
            $checked = $args['value'] === $radio_value ? 'checked' : '';
            ?>

            <div class="form-check">
                <input type="radio" id="<?= $args['name'] . '-' . $radio_value ?>" name="<?= $args['name'] ?>" value="<?= $radio_value ?>" class="form-check-input form-control <?= $args['class'] ?? '' ?>" <?= $checked ?> data-type="radio">
                <label class="form-check-label" for="<?= $args['name'] . '-' . $radio_value ?>"><?= $radio_label ?></label>
            </div>

            <?php
        }

        if (!empty($args['description'])) {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

    }

}
