<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Toggle {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';
        $data_dependencies = Pomatio_Framework_Helper::get_dependencies_data_attr($args);
        $random_id = Pomatio_Framework_Helper::generate_random_string(10, false);
        $id = $args['id'] . "-$random_id";

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

        ?>

        <div class="form-check">
            <div class="toggle-input">
                <input type="hidden" name="<?= $args['name'] ?>" value="no" disabled data-type="checkbox">
                <input<?= $disabled ?> type="checkbox" id="<?= $id ?>" name="<?= $args['name'] ?>" value="yes" class="form-check-input form-control toggle <?= $args['class'] ?? '' ?>" <?= $checked ?><?= $data_dependencies ?> data-type="toggle">
                <label class="toggle-btn <?= $args['class'] ?? '' ?>" for="<?= $id ?>"></label>
                <label for="<?= $id ?>"><?= $args['label'] ?></label>
            </div>

            <?php

            if (!empty($args['description'])) {
                echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
            }

            ?>
        </div>

        <?php

        wp_enqueue_style('pomatio-framework-toggle', POM_FORM_SRC_URI . '/dist/css/toggle.min.css');
    }

}
