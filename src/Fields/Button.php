<?php

namespace PomatioFramework\Fields;

class Button {

    public static function render_field(array $args): void {
        $label = !empty($args['text']) ? $args['text'] : '';
        $class = !empty($args['class']) ? ' class="' . $args['class'] . '"' : '';
        $type = isset($args['submit']) && $args['submit'] === true ? 'submit' : 'button';

        $link = isset($args['link']) && $args['link'] === true;
        $href = !empty($args['href']) ? $args['href'] : '';

        if ($link && !empty($href)) {
            ?>

            <a href="<?= $href ?>"<?= $class ?>><?= $label ?></a>

            <?php
        }
        else {
            ?>

            <input aria-label="<?= $label ?>" type="<?= $type ?>" id="<?= $args['id'] ?>"<?= $class ?> name="<?= $args['name'] ?>" value="<?= $label ?>">

            <?php
        }

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

}
