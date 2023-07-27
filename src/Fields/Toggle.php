<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Toggle {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';
        $data_dependencies = Pomatio_Framework_Helper::get_dependencies_data_attr($args);


        $checked = '';
        if (isset($args['value']) && $args['value'] === 'yes') {
            $checked = ' checked="checked"';
        }
        elseif (isset($args['default']) && $args['default'] === 'yes') {
            $checked = ' checked="checked"';
        }

        echo '<div class="form-check">';

        ?>

        <div class="row toggle-input">
            <div class="col-auto pr-15 pl-15">
                <input id="<?= $args['id'] ?>" type="checkbox" value="yes" class="web-toggle"<?= $checked ?><?= $data_dependencies ?> data-type="toggle"<?= $disabled ?>>
                <label class="web-toggle-btn <?= $args['class'] ?? '' ?>" for="<?= $args['id'] ?>"></label>
            </div>
            <div class="col">
                <label for="<?= $args['id'] ?>"><?= $args['label'] ?></label>
            </div>
        </div>

        <?php

        if (!empty($args['description'])) {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';
    }

}
