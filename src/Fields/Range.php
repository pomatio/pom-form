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
        if (isset($args['value']) && is_numeric($args['value'])) {
            $value = $args['value'];
        }
        elseif (isset($args['default']) && is_numeric($args['default'])) {
            $value = $args['default'];
        }

        $step = isset($args['step']) && is_numeric($args['step']) ? ' step="' . $args['step'] . '"' : '';
        $min = isset($args['min']) && is_numeric($args['min']) ? ' min="' . $args['min'] . '"' : '';
        $max = isset($args['max']) && is_numeric($args['max']) ? ' max="' . $args['max'] . '"' : '';

        ?>

        <div class="pomatio-framework-range">
            <input aria-label="<?= $args['label'] ?? '' ?>"<?= $step ?><?= $min ?><?= $max ?> type="range" id="<?= $args['id'] ?>" class="slider <?= $args['class'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" data-type="range"<?= $disabled ?>>
            <input aria-label="<?= $args['label'] ?? '' ?>" class="value" type="number" <?= $step ?><?= $min ?><?= $max ?> name="<?= $args['name'] ?>" value="<?= $value ?>">

            <?php

            if (isset($args['suffix']) && !empty($args['suffix'])) {
                ?>

                <span class="suffix"><?= $args['suffix'] ?></span>

                <?php
            }

            if (isset($args['default']) && !empty($args['default'])) {
                ?>

                <span class="restore-range" data-default="<?= $args['default'] ?>">
                    <span class="icon">
                        <span class="dashicons dashicons-undo"></span>
                    </span>
                    <span class="name"><?php _e('Restore default', 'pomatio-framework') ?></span>
                </span>

                <?php
            }

            ?>
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        wp_enqueue_style('pomatio-framework-range', POM_FORM_SRC_URI . '/dist/css/range.min.css');
        wp_enqueue_script('pomatio-framework-range',  POM_FORM_SRC_URI . '/dist/js/range' . POMATIO_MIN . '.js', ['jquery'], null, true);
    }

}
