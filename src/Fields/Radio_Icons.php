<?php

namespace PomatioFramework\Fields;

class Radio_Icons {

    public static function render_field(array $args): void {

        if (!isset($args['options'])) {
            return;
        }

        if (!empty($args['label'])) {
            echo '<label>' . $args['label'] . '</label>';
        }

        ?>

        <div class="pomatio-framework-radio-icons-wrapper">

            <?php

            foreach ($args['options'] as $radio_value => $radio_data) {
                $icon = '';
                if (file_exists($radio_data['icon'])) {
                    $icon = file_get_contents($radio_data['icon']);
                }

                $checked = checked((string)$args['value'], (string)$radio_value, false);

                ?>

                <label class="icon-wrapper">
                    <input type="radio" id="<?= $args['name'] . '-' . $radio_value ?>" name="<?= $args['name'] ?>" value="<?= $radio_value ?>" class="form-check-input form-control <?= $args['class'] ?? '' ?>" <?= $checked ?> data-type="radio">
                    <span class="label">
                        <span class="icon"><?= $icon ?></span>
                        <span class="description"><?= $radio_data['label'] ?></span>
                    </span>
                </label>

                <?php
            }

            ?>

        </div>

        <?php

        if (!empty($args['description'])) {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        wp_enqueue_style('pomatio-framework-radio_icons', POM_FORM_SRC_URI . '/dist/css/radio-icons.min.css');

    }

}
