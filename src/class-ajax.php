<?php

namespace POM\Form;

class POM_Form_Ajax {

    public function __construct() {
        add_action('wp_ajax_pom_form_get_icon_library_icons', [$this, 'get_library_icons']);
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
            ?>

            <li class="attachment" style="width: 8.3332%;">
                <div class="attachment-preview landscape">
                    <div class="thumbnail">
                        <div class="centered">
                            <img src="<?= POM_Form_Helper::path_to_url($file) ?>">
                        </div>
                    </div>
                </div>
            </li>

            <?php
        }

        echo '</ul>';

        wp_die(ob_get_clean());
    }

}
new POM_Form_Ajax();
