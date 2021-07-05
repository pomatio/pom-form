<?php

namespace POM\Form;

class Range {

    public static function render_field(array $args): void {

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        ?>

        <div class="range">
            <span>
                <input type="range" id="<?= $args['id'] ?>" class="slider <?= $args['class'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" <?= $args['custom_attrs'] ?>>
                <span class="value"><?= $args['value'] ?></span>
            </span>
        </div>

        <?php

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

}
