<?php

namespace POMFramework\Fields;

class Gallery {

    public static function render_field(array $args): void {
        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label><br>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        ?>

        <div class="gallery-wrapper">
            <div class="items-wrapper">
                <?php

                if (!empty($args['value'])) {
                    $ids = explode(',', $args['value']);
                    foreach ($ids as $id) {
                        $src = wp_get_attachment_thumb_url($id);

                        ?>

                        <div class="item-wrapper">
                            <span class="remove-selected-item dashicons dashicons-trash" data-id="<?= $id ?>"></span>
                            <img src="<?= $src ?>" alt="">
                        </div>

                        <?php
                    }

                }

                ?>
            </div>
            <span class="button open-gallery-modal"><?php _e('Select items', 'pom-framework') ?></span>
            <input type="hidden" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-control <?= $args['class'] ?? '' ?>">
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pom-framework-gallery', POM_FRAMEWORK_SRC_URI . '/dist/css/gallery.min.css');
        wp_enqueue_script('pom-framework-gallery',  POM_FRAMEWORK_SRC_URI . '/dist/js/gallery' . POM_FRAMEWORK_MIN . '.js', ['jquery'], null, true);
        wp_localize_script(
            'pom-framework-gallery',
            'pom_framework_gallery',
            [
                'title' => __('Select Media', 'pom-framework')
            ]
        );
    }

}
