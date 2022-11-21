<?php

namespace PomatioFramework\Fields;

class Radio {

    public static function render_field(array $args): void {

        if (!isset($args['options'])) {
            return;
        }

        if (!empty($args['label'])) {
            echo '<label>' . $args['label'] . '</label>';
        }

        ?>

        <ul class="pomatio-framework-radio-icons-wrapper">

        </ul>

        <?php

        foreach ($args['options'] as $radio_value => $radio_label) {
            if (wp_http_validate_url($radio_label)) {
                continue;
            }

            $checked = $args['value'] === $radio_value ? 'checked' : '';

            ?>

            <label class="form-check">
                <input type="radio" id="<?= $args['name'] . '-' . $radio_value ?>" name="<?= $args['name'] ?>" value="<?= $radio_value ?>" class="form-check-input form-control <?= $args['class'] ?? '' ?>" <?= $checked ?> data-type="radio">
                <span class="icon"><img src="<?= $radio_label ?>" alt="<?= $radio_label ?>"></span>
                <span class="form-check-label"><?= $radio_label ?></span>
            </label>

            <?php
        }

        if (!empty($args['description'])) {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

    }

}
