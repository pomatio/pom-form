<?php

namespace PomatioFramework\Fields;

class Range {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $value = '';
        if (isset($args['value']) && !empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (isset($args['default']) && !empty($args['default'])) {
            $value = $args['default'];
        }

        ?>

        <div class="range">
            <input aria-label="<?= $args['label'] ?? '' ?>" type="range" id="<?= $args['id'] ?>" class="slider <?= $args['class'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" data-type="range"<?= $disabled ?>>
            <span class="value"><?= $value ?></span>
            <?php

            if (isset($args['suffix']) && !empty($args['suffix'])) {
                ?>
                <span class="suffix"><?= $args['suffix'] ?></span>
                <?php
            }

            ?>
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        wp_enqueue_script('pom-form-range',  POM_FORM_SRC_URI . '/dist/js/range.min.js', ['jquery'], null, true);
    }

}
