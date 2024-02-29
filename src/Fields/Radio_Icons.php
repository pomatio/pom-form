<?php

namespace PomatioFramework\Fields;

class Radio_Icons {

    public static function render_field(array $args): void {
        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo "<label>{$args['label']}</label>";
        }

        $value = '';
        if (!empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (!empty($args['default'])) {
            $value = $args['default'];
        }

        ?>

        <div class="pomatio-framework-radio-icons-wrapper">

            <?php

            if (is_array($args['options']) && !empty($args['options'])) {
                foreach ($args['options'] as $radio_value => $radio_data) {
                    $icon = '';
                    if (file_exists($radio_data['icon'])) {
                        $icon = file_get_contents($radio_data['icon']);
                    }

                    $checked = checked($value, (string)$radio_value, false);

                    ?>

                    <label class="icon-wrapper">
                        <input type="radio" id="<?= $args['name'] . '-' . $radio_value ?>" name="<?= $args['name'] ?>" value="<?= $radio_value ?>" class="form-check-input form-control <?= $args['class'] ?? '' ?>" <?= $checked ?> data-type="radio_icons">
                        <span class="label">
                            <span class="icon"><?= $icon ?></span>
                            <span class="description"><?= $radio_data['label'] ?></span>
                        </span>
                    </label>

                    <?php
                }
            }

            ?>

            <label class="icon-wrapper restore-radio-icon" data-default="<?= $args['default'] ?>">
                <span class="label">
                    <span class="icon">
                        <span class="dashicons dashicons-undo"></span>
                    </span>
                    <span class="description"><?php _e('Restore default', 'pomatio-framework') ?></span>
                </span>
            </label>

        </div>

        <?php

        if (!empty($args['description'])) {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pomatio-framework-radio_icons', POM_FORM_SRC_URI . '/dist/css/radio-icons.min.css');
        wp_enqueue_script('pomatio-framework-color-palette',  POM_FORM_SRC_URI . '/dist/js/radio_icons' . POMATIO_MIN . '.js', [], null, true);
    }

}
