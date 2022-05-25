<?php

namespace POM\Form;

class POM_Form_Ajax {

    public function __construct() {
        add_action('wp_ajax_pom_form_get_icon_library_icons', [$this, 'get_library_icons']);
        add_action('wp_ajax_pom_form_get_icon_by_name', [$this, 'get_icon_by_name']);
    }

    public function get_library_icons(): void {
        $library = $_REQUEST['library'] ?? '';
        if (empty($library)) {
            wp_die('<span class="centered-text">' . __('Choose a library from the menu to see its icons or do a global search on all icon libraries.', 'pom-form') . '</span>');
        }

        $icons = POM_Form_Helper::get_icon_libraries();

        ob_start();

        echo '<ul class="attachments">';

        foreach (glob("{$icons[$library]['path']}$library/*.svg") as $file) {
            echo $this->get_icon_attachment_html($file);
        }

        echo '</ul>';

        wp_die(ob_get_clean());
    }

    public function get_icon_by_name(): void {
        $search = $_REQUEST['search'] ?? '';
        if (empty($search)) {
            wp_die('<span class="centered-text">' . __('Choose a library from the menu to see its icons or do a global search on all icon libraries.', 'pom-form') . '</span>');
        }

        $icons = POM_Form_Helper::get_icon_libraries();

        ob_start();

        echo '<ul class="attachments">';


        foreach ($icons as $icon_library => $data) {
            foreach(glob("{$data['path']}$icon_library/*$search*.svg") as $file) {
                echo $this->get_icon_attachment_html($file);
            }
        }


        echo '</ul>';

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

}
new POM_Form_Ajax();
