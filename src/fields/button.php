<?php

namespace PomatioFramework\fields;

class Button {

    public static function render_field(array $args): void {

        //unset($args['value']);
        $label = !empty($args['label']) ? $args['label'] : '';

        ?>

        <input type="button" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $label ?>">

        <?php

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

}
