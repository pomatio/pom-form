<?php

namespace PomatioFramework\Fields;

class Button {

    public static function render_field(array $args): void {
        $label = !empty($args['label']) ? $args['label'] : '';
        $class = !empty($args['class']) ? ' class="' . $args['class'] . '"' : '';
        $type = !empty($args['type']) ? $args['type'] : 'button';

        ?>

        <input aria-label="<?= $label ?>" type="<?= $type ?>" id="<?= $args['id'] ?>"<?= $class ?> name="<?= $args['name'] ?>" value="<?= $label ?>">

        <?php

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

}
