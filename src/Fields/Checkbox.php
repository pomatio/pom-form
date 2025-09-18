<?php

namespace PomatioFramework\Fields;

class Checkbox {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';

        if (!empty($args['options']) && is_array($args['options'])) {
            if (!empty($args['label'])) {
                echo '<label>' . $args['label'] . '</label>';
            }

            if (!empty($args['description']) && $args['description_position'] === 'below_label') {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

            echo '<input type="hidden" name="' . $args["name"] . '" value="no" disabled>';
            foreach ($args['options'] as $option_value => $option_label) {
                $checked = (is_array($args['value']) && !empty($args['value']) && in_array($option_value, $args['value'], true)) || $option_value === $args['value'] ? 'checked="checked"' : '';

                ?>

                <div class="form-group">
                    <input<?= $disabled ?> type="checkbox" id="<?= $args['id'] . '-' . $option_value ?>" name="<?= $args['name'] ?>[]" value="<?= $option_value ?>" class="form-check-input form-control <?= $args['class'] ?? '' ?>" <?= $checked ?> data-type="checkbox">
                    <label class="form-check-label" for="<?= $args['id'] . '-' . $option_value ?>"><?= $option_label ?></label>
                </div>

                <?php
            }
        }
        else {
            $checked = '';
            if (!empty($args['value'])) {
                if ($args['value'] === 'yes') {
                    $checked = 'checked="checked"';
                }
            }
            elseif (!empty($args['default'])) {
                if ($args['default'] === 'yes') {
                    $checked = 'checked="checked"';
                }
            }

            $checkbox_label = $args['label_checkbox'] ?? $args['label'] ?? '';

            ?>

            <div class="form-group">
                <input type="hidden" name="<?= $args['name'] ?>" value="no" disabled data-type="checkbox">
                <input<?= $disabled ?> type="checkbox" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="yes" class="form-check-input form-control <?= $args['class'] ?? '' ?>" <?= $checked ?> data-type="checkbox">
                <?php if (!empty($checkbox_label)) { ?>
                    <label class="form-check-label" for="<?= $args['id'] ?>"><?= $checkbox_label ?></label>
                <?php } ?>
            </div>

            <?php
        }

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }
    }

}
