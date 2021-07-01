<?php

namespace POM\Form;

class Text {

    public static function render_field(array $args): void {

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        ?>

        <input type="text" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" <?= $args['custom_attrs'] ?>>

        <?php

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

}
