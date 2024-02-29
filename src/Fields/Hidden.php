<?php

namespace PomatioFramework\Fields;

class Hidden {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        $value = '';
        if (!empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (!empty($args['default'])) {
            $value = $args['default'];
        }

        ?>

        <input type="hidden" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" class="form-control <?= $args['class'] ?? '' ?>" data-type="hidden"<?= $disabled ?>>

        <?php
    }

}
