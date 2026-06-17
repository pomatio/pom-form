<?php

namespace POMFramework\Fields;

use POMFramework\POM_Framework_Helper;

class Quantity {

    public static function render_field(array $args): void {
        $disabled = isset($args['disabled']) && $args['disabled'] === true ? ' disabled' : '';
        $data_dependencies = POM_Framework_Helper::get_dependencies_data_attr($args);

        echo '<div class="form-group">';

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

        ?>

        <div class="pom-framework-quantity-wrapper">
            <span class="number-down"></span>
            <input aria-label="<?= $args['label'] ?? '' ?>" type="number" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" class="form-control input-text qty text <?= $args['class'] ?? '' ?>" pattern="[0-9]*" inputmode="numeric" aria-labelledby="" data-type="quantity"<?= $disabled ?><?= $data_dependencies ?>>
            <span class="number-up"></span>
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_script('pom-framework-quantity', POM_FRAMEWORK_SRC_URI . '/dist/js/quantity' . POM_FRAMEWORK_MIN . '.js', ['jquery'], null, true);
    }

}
