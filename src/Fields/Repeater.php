<?php
/**
 * New repeater elements are added via ajax.
 * If changes are made to the HTML of a repeater element,
 * update it in Pomatio_Framework_Ajax.php as well --> get_repeater_item_html().
 */

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;
use PomatioFramework\Pomatio_Framework;

class Repeater {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description">' . $args['description'] . '</small>';
        }

        $repeater_config = [
            'title' => $args['title'],
            'fields' => $args['fields']
        ];
        if (isset($args['limit'])) {
            $repeater_config['limit'] = $args['limit'];
        }
        if (isset($args['cloneable'])) {
            $repeater_config['cloneable'] = $args['cloneable'];
        }
        $repeater_config = base64_encode(json_encode($repeater_config));

        //$json = json_decode(htmlspecialchars_decode($args['value']), true);
        $json = $args['value'];

        $sortable = isset($args['sortable']) && $args['sortable'] === true ? ' sortable' : '';

        $repeater_identifier = Pomatio_Framework_Helper::generate_random_string(10, false);

        $limit = isset($args['limit']) ? ' data-limit="' . (int)$args['limit'] . '"' : '';

        ?>

        <div class="repeater-wrapper<?= $sortable ?>"<?= $limit ?>>

            <?php

            // Render defaults if set
            if (empty($json['default']) && isset($args['defaults']) && !empty($args['defaults'])) {
                $defaults_identifier = Pomatio_Framework_Helper::generate_random_string(10, false);

                foreach ($args['defaults'] as $default) {
                    $default_json = htmlspecialchars(json_encode($default), ENT_QUOTES, 'UTF-8');

                    ?>

                    <div class="repeater default closed">
                        <div class="title">
                            <strong><?= $args['title'] ?></strong><span></span>
                        </div>
                        <div class="repeater-fields">
                            <input type="hidden" name="repeater_identifier" value="<?= $defaults_identifier ?>">
                            <input type="hidden" name="default_values" value="<?= $default_json ?>">

                            <?php

                            foreach ($args['fields'] as $field) {
                                $field['value'] = $default[$field['name']]['value'];
                                $field['disabled'] = $default[$field['name']]['disabled'];
                                echo (new Pomatio_Framework())::add_field($field);
                            }

                            ?>

                            <div class="repeater-action-row">
                                <span class="restore-default"><?php _e('Restore default', 'pomatio-framework') ?></span>

                                <?php

                                if (isset($args['cloneable']) && $args['cloneable'] === true) {
                                    ?>

                                    <span class="clone-repeater"><?php _e('Clone', 'pomatio-framework') ?></span>

                                    <?php
                                }

                                if (isset($default['can_be_removed']) && $default['can_be_removed']) {
                                    ?>

                                    <span class="delete"><?php _e('Delete', 'pomatio-framework') ?></span>

                                    <?php
                                }

                                ?>

                            </div>
                        </div>
                    </div>

                    <?php
                }
            }

            // Render the saved values
            if (!empty($json)) {
                foreach ($json as $repeater_type => $repeater_elements) {
                    if (!empty($repeater_elements) && count($repeater_elements) > 0) {
                        foreach ($repeater_elements as $repeater_item) {
                            ?>

                            <div class="repeater <?= $repeater_type ?> closed">
                                <div class="title"><strong><?= $args['title'] ?></strong><span></span></div>
                                <div class="repeater-fields">
                                    <input type="hidden" name="repeater_identifier" value="<?= $repeater_item['repeater_identifier'] ?? $repeater_identifier ?>">

                                    <?php

                                    if ($repeater_type === 'default') {
                                        ?>

                                        <input type="hidden" name="default_values" value="<?= htmlspecialchars(json_encode($repeater_item['default_values']), ENT_QUOTES, 'UTF-8') ?? '' ?>">


                                        <?php
                                    }

                                    foreach ($args['fields'] as $field) {
                                        if (array_key_exists($field['name'], $repeater_item)) {
                                            if ($field['type'] === 'repeater') {
                                                $field['value'] = htmlspecialchars(json_encode($repeater_item[$field['name']]['value']), ENT_QUOTES, 'UTF-8');
                                            }
                                            else {
                                                $field['value'] = html_entity_decode(htmlspecialchars($repeater_item[$field['name']]['value'], ENT_QUOTES, 'UTF-8'), ENT_HTML5);
                                            }
                                        }

                                        if ($repeater_type === 'default' && $repeater_item['default_values'][$field['name']]['disabled']) {
                                            $field['disabled'] = true;
                                        }

                                        echo (new Pomatio_Framework())::add_field($field);
                                    }

                                    echo '<div class="repeater-action-row">';

                                        if ($repeater_type === 'default' && isset($repeater_item['default_values']) && !empty($repeater_item['default_values'])) {
                                            ?>

                                            <span class="restore-default"><?php _e('Restore default', 'pomatio-framework') ?></span>

                                            <?php
                                        }

                                        if (isset($args['cloneable']) && $args['cloneable'] === true) {
                                            ?>

                                            <span class="clone-repeater"><?php _e('Clone', 'pomatio-framework') ?></span>

                                            <?php
                                        }

                                        if ($repeater_type === 'default' && isset($repeater_item['default_values']['can_be_removed']) && $repeater_item['default_values']['can_be_removed']) {
                                            ?>

                                            <span class="delete"><?php _e('Delete', 'pomatio-framework') ?></span>

                                            <?php
                                        }
                                        elseif ($repeater_type === 'new') {
                                            ?>

                                            <span class="delete"><?php _e('Delete', 'pomatio-framework') ?></span>

                                            <?php
                                        }

                                    echo '</div>';

                                    ?>
                                </div>
                            </div>

                            <?php
                        }
                    }
                }
            }

            ?>

            <button class="button add-new-repeater-item"><?php _e('Add new', 'pomatio-framework') ?></button>
            <img class="repeater-spinner" src="<?= admin_url('images/loading.gif') ?>" alt="Spinner">

            <?php

            if (isset($args['defaults']) && !empty($args['defaults'])) {
                ?>

                <button class="button button-secondary right restore-repeater-defaults" data-title="<?= $args['title'] ?>" data-fields="<?= base64_encode(json_encode($args['fields'])) ?>" data-defaults="<?= base64_encode(json_encode($args['defaults'])) ?>"><?php _e('Restore defaults', 'pomatio-framework') ?></button>

                <?php
            }

            ?>

            <input type="hidden" name="config" value="<?= $repeater_config ?>">
            <input type="hidden" name="<?= $args['name'] ?>" value="<?= htmlspecialchars(json_encode($args['value']), ENT_QUOTES, 'UTF-8') ?>" class="repeater-value">
        </div>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pom-form-repeater', POM_FORM_SRC_URI . '/dist/css/repeater.min.css');
        wp_enqueue_script('pom-form-repeater', POM_FORM_SRC_URI . '/dist/js/repeater.min.js', ['jquery'], null, true);
        wp_localize_script(
            'pom-form-repeater',
            'pom_form_repeater',
            [
                'limit' => __('Element limit reached', 'pomatio-framework'),
                'restore_msg' => __('Are you sure you want to reset the repeaters?', 'pomatio-framework'),
                'delete_repeater' => __('Are you sure you want to delete this repeater?', 'pomatio-framework'),
            ]
        );
    }

}
