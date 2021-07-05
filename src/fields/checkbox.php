<?php

namespace POM\Form;

class Checkbox {

    public static function render_field(array $args): void {

        $checked = isset($args['value']) && $args['value'] !== false && $args['value'] !== 'false' && $args['value'] !== '' ? 'checked="checked"' : '';

        echo '<div class="form-check">';

            ?>

            <input type="checkbox" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-check-input form-control <?= $args['class'] ?? '' ?>" <?= $args['custom_attrs'] ?: '' ?> <?= $checked ?>>
            <label class="form-check-label" for="<?= $args['id'] ?>"><?= $args['label'] ?></label>

            <?php

            if (!empty($args['description'])) {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

        echo '</div>';

    }

}
