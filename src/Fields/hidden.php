<?php

namespace PomatioFramework\Fields;

class Hidden {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        ?>

        <input type="hidden" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-control <?= $args['class'] ?? '' ?>" data-type="hidden"<?= $disabled ?>>

        <?php
    }

}
