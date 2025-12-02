<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

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
        if (!empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (!empty($args['default'])) {
            $value = $args['default'];
        }

        $data_dependencies = Pomatio_Framework_Helper::get_dependencies_data_attr($args);
        $unique_field_name = $args['name'] . '_' . Pomatio_Framework_Helper::generate_random_string(6, false);

        echo '<div class="color-palette-wrapper"' . $data_dependencies . ' data-base-name="' . esc_attr($args['name']) . '">';

        if (!empty($colors)) {
            $i = 0;

            foreach ($colors as $color_slug => $color_data) {
                $checked = checked($value, (string)$color_slug, false);

                ?>

                <?php $input_id = $unique_field_name . '_' . $i; ?>
                <input type="radio" name="<?= $unique_field_name ?>" data-base-name="<?= esc_attr($args['name']) ?>" id="<?= $input_id ?>" value="<?= $color_slug ?>" data-type="color_palette" <?= $checked ?><?= $data_dependencies ?>>
                <label style="background-color: <?= $color_data['hex'] ?? '#f3f3f3' ?>" for="<?= $input_id ?>">

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

        if (!empty($args['default'])) {
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
