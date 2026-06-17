<?php

namespace POMFramework\Fields;

class Image_Picker {

    public static function render_field(array $args): void {
        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label><br>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $value = '';
        if (!empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (!empty($args['default'])) {
            $value = $args['default'];
        }

        ?>

        <div class="pom-framework-image-wrapper">
            <span class="remove-selected-image dashicons dashicons-trash"></span>
            <div class="image-wrapper">
                <?php

                if (!empty($value)) {
                    echo '<img width="280px" alt="" src="' . $value . '">';
                }

                ?>
            </div>
            <input aria-label="image-url" type="url" name="<?= $args['name'] ?>" value="<?= $value ?>" data-type="image_picker">
            <span class="button open-image-picker"><?php _e('Select image', 'pom-framework') ?></span>
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_media();
        wp_enqueue_style('pom-framework-image_picker', POM_FRAMEWORK_SRC_URI . '/dist/css/image-picker.min.css');
        wp_enqueue_script('pom-framework-image_picker',  POM_FRAMEWORK_SRC_URI . '/dist/js/image_picker' . POM_FRAMEWORK_MIN . '.js', ['jquery'], null, true);
        wp_localize_script(
            'pom-framework-image_picker',
            'pom_framework_image_picker',
            [
                'title' => __('Choose Image', 'pom-framework'),
                'button' => __('Choose Image', 'pom-framework'),
            ]
        );
    }

}
