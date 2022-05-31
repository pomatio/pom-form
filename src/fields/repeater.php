<?php

namespace POM\Form;

class Repeater {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $repeater_config = [
            'title' => $args['title'],
            'fields' => $args['fields']
        ];
        $repeater_config = base64_encode(json_encode($repeater_config));

        ?>

        <div class="repeater-wrapper">
            <div class="repeater closed" data-name="<?= $args['name'] ?>">
                <div class="title"><?= $args['title'] ?></div>
                <div class="repeater-fields">
                    <?php

                    foreach ($args['fields'] as $field) {
                        echo (new Form())::add_field($field);
                    }

                    ?>

                    <span class="delete"><?php _e('Delete', 'pom-form') ?></span>
                </div>
            </div>
            <button class="button add-new-repeater-item"><?php _e('Add new', 'pom-form') ?></button>
            <img class="repeater-spinner" src="<?= admin_url('images/loading.gif') ?>" alt="Spinner">
            <input type="hidden" name="config" value="<?= $repeater_config ?>">
            <input type="hidden" name="<?= $args['name'] ?>" value="" class="repeater-value">
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pom-form-repeater', POM_FORM_SRC_URI . '/dist/css/repeater.min.css');
        wp_enqueue_script('pom-form-repeater',  POM_FORM_SRC_URI . '/dist/js/repeater.js', [], null, true);

    }

}
