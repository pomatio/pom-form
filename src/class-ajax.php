<?php

namespace POM\Form;

class POM_Form_Ajax {

    public function __construct() {
        add_action('wp_ajax_pom_form_get_icon_library_icons', [$this, 'get_library_icons']);
        add_action('wp_ajax_pom_form_get_icon_by_name', [$this, 'get_icon_by_name']);

        add_action('wp_ajax_pom_form_get_repeater_item_html', [$this, 'get_repeater_item_html']);
    }

    public function get_library_icons(): void {
        $library = $_REQUEST['library'] ?? 'all';
        if (empty($library)) {
            wp_die('<span class="centered-text">' . __('Choose a library from the menu to see its icons or do a global search on all icon libraries.', 'pom-form') . '</span>');
        }

        $icons = POM_Form_Helper::get_icon_libraries();

        ob_start();

        echo '<ul class="attachments">';

        if ($library === 'all') {
            foreach ($icons as $library_index => $data) {
                foreach (glob("{$data['path']}$library_index/*.svg") as $file) {
                    echo $this->get_icon_attachment_html($file);
                }
            }
        }
        else {
            foreach (glob("{$icons[$library]['path']}$library/*.svg") as $file) {
                echo $this->get_icon_attachment_html($file);
            }
        }

        echo '</ul>';

        wp_die(ob_get_clean());
    }

    public function get_icon_by_name(): void {
        $search = $_REQUEST['search'] ?? '';
        $library = $_REQUEST['library'] ?? 'all';

        /**
         * If the search is empty all icons are returned.
         */
        if (empty($search)) {
            add_action('wp_ajax_pom_form_get_icon_library_icons', [$this, 'get_library_icons']);
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

        if (empty($config)) {
            wp_die();
        }

        $config = json_decode(base64_decode($config), true);

        ob_start();

        ?>

        <div class="repeater">
            <div class="title"><strong><?= $config['title'] ?></strong><span></span></div>
            <div class="repeater-fields">
                <?php

                foreach ($config['fields'] as $field) {
                    echo (new Form())::add_field($field);
                }

                ?>
                <span class="delete"><?php _e('Delete', 'pom-form') ?></span>
            </div>
        </div>

        <?php

        wp_die(ob_get_clean());
    }

}
new POM_Form_Ajax();
