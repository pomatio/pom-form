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
            wp_die($library);
        }

        $icons_dir = POM_Form_Helper::get_icon_libraries_path();

        ob_start();

        echo '<ul class="attachments">';

        foreach(glob("$icons_dir$library/*.svg") as $file) {
            echo $this->get_icon_attachment_html($file);
        }

        echo '</ul>';

        wp_die(ob_get_clean());
    }

    public function get_icon_by_name(): void {
        $search = $_REQUEST['search'] ?? '';
        if (empty($search)) {
            wp_die($search);
        }

        $icons_dir = POM_Form_Helper::get_icon_libraries_path();
        $icon_libraries = POM_Form_Helper::get_icon_libraries();

        ob_start();

        echo '<ul class="attachments">';


        foreach ($icon_libraries as $icon_library => $label) {
            foreach(glob("$icons_dir$icon_library/$search*.svg") as $file) {
                echo $this->get_icon_attachment_html($file);
            }
        }


        echo '</ul>';

        wp_die(ob_get_clean());

    }

    private function get_icon_attachment_html($file) {
        ob_start();

        ?>

        <li class="attachment" style="width: 8.3332%;">
            <div class="attachment-preview landscape">
                <div class="thumbnail">
                    <div class="centered">
                        <img alt="" src="<?= POM_Form_Helper::path_to_url($file) ?>">
                    </div>
                </div>
            </div>
        </li>

        <?php

        return ob_get_clean();
    }

}
new POM_Form_Ajax();
