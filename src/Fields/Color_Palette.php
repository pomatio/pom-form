<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Color_Palette {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $colors = Pomatio_Framework_Helper::get_color_palette();

        echo '<div class="color-palette-wrapper">';

        $i = 0;
        foreach ($colors as $color) {
            $checked = checked((string)$args['value'], (string)$color, false);

            ?>

            <input type="radio" name="<?= $args['name'] ?>" id="<?= $args['name'] . '_' . $i ?>" value="<?= $color ?>" <?= $checked ?>>
            <label style="background-color: <?= $color ?? '#f3f3f3' ?>" for="<?= $args['name'] . '_' . $i ?>"></label>

            <?php

            $i++;
        }

        echo '</div>';

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pomatio-framework-color-palette', POM_FORM_SRC_URI . '/dist/css/color-palette.min.css');

    }

}
