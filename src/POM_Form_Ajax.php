<?php

namespace PomatioFramework;

class POM_Form_Ajax {

    public function __construct() {
        add_action('wp_ajax_pom_form_get_icon_library_icons', [$this, 'get_library_icons']);
        add_action('wp_ajax_pom_form_get_icon_by_name', [$this, 'get_icon_by_name']);
        add_action('wp_ajax_pom_form_get_repeater_item_html', [$this, 'get_repeater_item_html']);
        add_action('wp_ajax_pom_form_restore_repeater_defaults', [$this, 'restore_repeater_defaults']);
    }

    public function get_library_icons(): void {
        $library = $_REQUEST['library'] ?? 'all';
        $limit = 55;
        $current_offset = isset($_REQUEST['offset']) ? (int)$_REQUEST['offset'] : 0;

        if (empty($library)) {
            wp_die('<span class="centered-text">' . __('Choose a library from the menu to see its icons or do a global search on all icon libraries.', 'pom-form') . '</span>');
        }


        $icons = POM_Form_Helper::get_icon_libraries();
        $glob = [];

        ob_start();

        echo '<ul class="attachments">';

        if ($library === 'all') {
            foreach ($icons as $library_index => $data) {
                $icons_array = glob("{$data['path']}$library_index/*.svg");
                $glob = array_merge($glob, $icons_array);

                /**
                 * As long as there is no offset, it is not necessary to load all the icons.
                 * Only the first until reaching the limit.
                 */
                if ($current_offset === 0 && count($glob) > $limit) {
                    break;
                }
            }

            if (!empty($glob)) {
                foreach (array_slice($glob, $current_offset, $limit) as $file) {
                    echo $this->get_icon_attachment_html($file);
                }
            }
        }
        else {
            $glob = glob("{$icons[$library]['path']}$library/*.svg");
            if (!empty($glob)) {
                foreach (array_slice($glob, $current_offset, $limit) as $file) {
                    echo $this->get_icon_attachment_html($file);
                }
            }
        }

        echo '</ul>';

        $glob_count = count($glob);
        if ($glob_count > ($current_offset + $limit)) {
            ?>

            <div class="load-more-icons">
                <button class="button button-secondary" data-total="<?= $glob_count ?>" data-offset="<?= $current_offset ?>"><?php _e('Load more', 'pom-form') ?></button>
                <img class="icon-picker-spinner" src="<?= admin_url('images/loading.gif') ?>" style="display: none; padding-top: 7px;" alt="Spinner">
            </div>

            <?php
        }

        wp_die(ob_get_clean());
    }

    public function get_icon_by_name(): void {
        $search = $_REQUEST['search'] ?? '';
        $library = $_REQUEST['library'] ?? 'all';

        /**
         * If the search is empty all icons are returned.
         */
        if (empty($search)) {
            $this->get_library_icons();
        }

        $icons = POM_Form_Helper::get_icon_libraries();
        $found_files = 0;

        ob_start();

        echo '<ul class="attachments">';

        if ($library === 'all') {
            foreach ($icons as $icon_library => $data) {
                foreach (glob("{$data['path']}$icon_library/*$search*.svg") as $file) {
                    echo $this->get_icon_attachment_html($file);
                    $found_files++;
                }
            }
        }
        else {
            $library_data = $icons[$library];
            foreach (glob("{$library_data['path']}$library/*$search*.svg") as $file) {
                echo $this->get_icon_attachment_html($file);
                $found_files++;
            }
        }

        echo '</ul>';

        if ($found_files === 0) {
            ob_get_clean();
            wp_die('<span class="centered-text">' . __('No icons found that match the search criteria.', 'pom-form') . '</span>');
        }

        wp_die(ob_get_clean());
    }

    private function get_icon_attachment_html($file) {
        ob_start();

        ?>

        <li class="attachment" style="width: 9%;">
            <div class="attachment-preview landscape">
                <div class="thumbnail">
                    <div class="centered">
                        <img alt="" src="<?= POM_Form_Helper::path_to_url($file) ?>">
                        <span class="icon-name"><?= pathinfo($file, PATHINFO_FILENAME) ?></span>
                    </div>
                </div>
            </div>
        </li>

        <?php

        return ob_get_clean();
    }

    public function get_repeater_item_html(): void {
        $config = $_REQUEST['config'] ?? '';
        $items = $_REQUEST['items'] ?? '';

        if (empty($config)) {
            wp_die();
        }

        $config = json_decode(base64_decode($config), true);

        // Avoid a new item if the limit has been reached.
        if (!empty($items) && !empty($config['limit']) && (int)$items >= (int)$config['limit']) {
            wp_die();
        }

        ob_start();

        ?>

        <div class="repeater new">
            <div class="title"><strong><?= $config['title'] ?></strong><span></span></div>
            <div class="repeater-fields">
                <input type="hidden" name="repeater_identifier" value="<?= POM_Form_Helper::generate_random_string(10, false) ?>">

                <?php

                foreach ($config['fields'] as $field) {
                    echo (new Pomatio_Framework())::add_field($field);
                }

                ?>

                <div class="repeater-action-row">

                    <?php

                    if (isset($config['cloneable']) && $config['cloneable'] === true) {
                        ?>

                        <span class="clone-repeater"><?php _e('Clone', 'pom-form') ?></span>

                        <?php
                    }

                    ?>

                    <span class="delete"><?php _e('Delete', 'pom-form') ?></span>
                </div>
            </div>
        </div>

        <?php

        wp_die(ob_get_clean());
    }

    public function restore_repeater_defaults(): void {
        $defaults = $_REQUEST['defaults'] ?? '';
        $fields = $_REQUEST['fields'] ?? '';
        $title = $_REQUEST['title'] ?? '';

        if (empty($defaults)) {
            wp_die($defaults);
        }

        ob_start();

        $defaults = json_decode(base64_decode($defaults), true);
        $fields = json_decode(base64_decode($fields), true);
        foreach ($defaults as $default) {
            $default_json = htmlspecialchars(json_encode($default), ENT_QUOTES, 'UTF-8');
            $defaults_identifier = POM_Form_Helper::generate_random_string(10, false);

            ?>

            <div class="repeater default closed">
                <div class="title">
                    <strong><?= $title ?></strong><span></span>
                </div>
                <div class="repeater-fields">
                    <input type="hidden" name="repeater_identifier" value="<?= $defaults_identifier ?>">
                    <input type="hidden" name="default_values" value="<?= $default_json ?>">

                    <?php

                    foreach ($fields as $field) {
                        $field['value'] = $default[$field['name']]['value'];
                        $field['disabled'] = $default[$field['name']]['disabled'];
                        echo (new Pomatio_Framework())::add_field($field);
                    }

                    ?>

                    <div class="repeater-action-row">
                        <span class="restore-default"><?php _e('Restore default', 'pom-form') ?></span>

                        <?php

                        if (isset($default['can_be_removed']) && $default['can_be_removed']) {
                            ?>

                            <span class="delete"><?php _e('Delete', 'pom-form') ?></span>

                            <?php
                        }

                        ?>

                    </div>
                </div>
            </div>

            <?php
        }

        wp_die(ob_get_clean());
    }

}
new POM_Form_Ajax();
