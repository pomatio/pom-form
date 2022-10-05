<?php

namespace PomatioFramework\Fields;

class Image_Picker {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        ?>

        <div class="pomatio-framework-image-wrapper">
            <span class="remove-selected-image dashicons dashicons-trash"></span>
            <div class="image-wrapper">
                <?php

                if (!empty($args['value'])) {
                    echo '<img width="280px" alt="" src="' . $args['value'] . '">';
                }

                ?>
            </div>
            <input aria-label="image-url" type="url" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>">
            <button class="button open-image-picker"><?php _e('Select image', 'pomatio-framework') ?></button>
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pomatio-framework-image_picker', POM_FORM_SRC_URI . '/dist/css/image-picker.min.css');
        wp_enqueue_script('pomatio-framework-image_picker',  POM_FORM_SRC_URI . '/dist/js/image_picker.min.js', ['jquery'], null, true);
        wp_localize_script(
            'pomatio-framework-image_picker',
            'pom_form_image_picker',
            [
                'title' => __('Choose Image', 'pomatio-framework'),
                'button' => __('Choose Image', 'pomatio-framework'),
            ]
        );

    }

}
