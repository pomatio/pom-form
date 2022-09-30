<?php

namespace PomatioFramework\Fields;

class Toggle {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        $checked = isset($args['value']) && $args['value'] !== false && $args['value'] !== 'false' && $args['value'] !== '' ? 'checked="checked"' : '';

        echo '<div class="form-check">';

        ?>

        <div class="row toggle-input">
            <div class="col-auto pr-15 pl-15">
                <input id="<?= $args['id'] ?>" type="checkbox" class="web-toggle" <?= $checked ?> data-type="toggle"<?= $disabled ?>>
                <label class="web-toggle-btn <?= $args['class'] ?? '' ?>" for="<?= $args['id'] ?>"></label>
            </div>
            <div class="col">
                <label for="<?= $args['id'] ?>"><?= $args['label'] ?></label>
            </div>
        </div>

        <?php

        if (!empty($args['description'])) {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';
    }

}
