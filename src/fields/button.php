<?php

namespace POM\Form;

class Button {

    public static function render_field(array $args): void {

        //unset($args['value']);
        $label = !empty($args['label']) ? $args['label'] : '';

        ?>

        <input type="button" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $label ?>" <?= $args['custom_attrs'] ?>>

        <?php

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

}
