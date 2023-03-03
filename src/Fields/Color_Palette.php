<?php

namespace PomatioFramework\Fields;

class Color_Palette {

    public static function render_field(array $args): void {
        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label><br>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $colors = $args['options'] ?? [];

        $value = '';
        if (isset($args['value']) && !empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (isset($args['default']) && !empty($args['default'])) {
            $value = $args['default'];
        }

        echo '<div class="color-palette-wrapper">';

        if (!empty($colors)) {
            $i = 0;

            foreach ($colors as $color_slug => $color_data) {
                $checked = checked($value, (string)$color_slug, false);

                ?>

                <input type="radio" name="<?= $args['name'] ?>" id="<?= $args['name'] . '_' . $i ?>" value="<?= $color_slug ?>" <?= $checked ?>>
                <label style="background-color: <?= $color_data['hex'] ?? '#f3f3f3' ?>" for="<?= $args['name'] . '_' . $i ?>">

                    <?php

                    if (isset($color_data['icon']) && file_exists($color_data['icon'])) {
                        $icon = file_get_contents($color_data['icon']);

                        ?>

                        <span class="icon"><?= $icon ?></span>

                        <?php
                    }

                    ?>

                    <span class="name"><?= $color_data['label'] ?? $color_slug ?></span>
                </label>

                <?php

                $i++;
            }
        }

        if (isset($args['default']) && !empty($args['default'])) {
            ?>

            <label class="restore-color-palette" data-default="<?= $args['default'] ?>">
                <span class="icon">
                    <span class="dashicons dashicons-undo"></span>
                </span>
                <span class="name"><?php _e('Restore default', 'pomatio-framework') ?></span>
            </label>

            <?php
        }

        echo '</div>';

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pomatio-framework-color-palette', POM_FORM_SRC_URI . '/dist/css/color-palette.min.css');
        wp_enqueue_script('pomatio-framework-color-palette',  POM_FORM_SRC_URI . '/dist/js/color_palette' . POMATIO_MIN . '.js', [], null, true);
    }

}
