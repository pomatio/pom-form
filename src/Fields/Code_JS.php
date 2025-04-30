<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Code_JS {

    public static function render_field(array $args): void {
        $data_dependencies = Pomatio_Framework_Helper::get_dependencies_data_attr($args);

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label><br>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $value = '';
        if (file_exists($args['value'])) {
            $value = file_get_contents($args['value']);
        }
        elseif (!empty($args['default'])) {
            $value = $args['default'];
        }

        ?>

        <textarea aria-label="<?= $args['label'] ?>" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" class="form-control pomatio-framework-code-editor-js <?= $args['class'] ?>"<?= $data_dependencies ?> data-type="code_js"><?= $value ?></textarea>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        $codemirror_settings = wp_enqueue_code_editor([]);
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
        wp_enqueue_script('pomatio-framework-code', POM_FORM_SRC_URI . '/dist/js/code' . POMATIO_MIN . '.js', ['jquery', 'wp-theme-plugin-editor'], null, true);
        wp_localize_script(
            'pomatio-framework-code',
            'settings',
            [
                'codeMirrorSettings' => $codemirror_settings
            ]
        );
    }

}
