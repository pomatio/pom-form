<?php

namespace POM\Form;

class Hidden {

    public static function render_field(array $args): void {
        ?>

        <input type="hidden" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-control <?= $args['class'] ?? '' ?>">

        <?php
    }

}
