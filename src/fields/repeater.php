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

        $json = json_decode(htmlspecialchars_decode($args['value']), true);

        $sortable = isset($args['sortable']) && $args['sortable'] ? ' sortable' : '';

        ?>

        <div class="repeater-wrapper<?= $sortable ?>">

            <?php

            if (count($json) > 0) {
                foreach ($json as $repeater_item) {
                    ?>

                    <div class="repeater closed" data-name="<?= $args['name'] ?>">
                        <div class="title"><strong><?= $args['title'] ?></strong><span></span></div>
                        <div class="repeater-fields">
                            <?php

                            foreach ($args['fields'] as $field) {
                                if (array_key_exists($field['name'], $repeater_item)) {
                                    $field['value'] = $repeater_item[$field['name']];
                                }
                                echo (new Form())::add_field($field);
                            }

                            ?>

                            <span class="delete"><?php _e('Delete', 'pom-form') ?></span>
                        </div>
                    </div>

                    <?php
                }
            }
            else {
                ?>

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

                <?php
            }

            ?>

            <button class="button add-new-repeater-item"><?php _e('Add new', 'pom-form') ?></button>
            <img class="repeater-spinner" src="<?= admin_url('images/loading.gif') ?>" alt="Spinner">
            <input type="hidden" name="config" value="<?= $repeater_config ?>">
            <input type="hidden" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="repeater-value">
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
