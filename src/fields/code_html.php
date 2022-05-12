<?php

namespace POM\Form;

class Code_HTML {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        ?>

        <textarea id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" class="form-control pom-form-code-editor-html <?= $args['class'] ?>" <?= $args['custom_attrs'] ?>><?= stripslashes($args['value']) ?></textarea>

        <?php

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        $codemirror_settings = wp_enqueue_code_editor([]);
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
        wp_enqueue_script('pom-form-code', POM_FORM_SRC_URI . '/dist/js/code.js', ['jquery', 'wp-theme-plugin-editor'], POM_FORM_VERSION, true);
        wp_localize_script(
            'pom-form-code',
            'settings',
            [
                'codeMirrorSettings' => $codemirror_settings
            ]
        );

    }

}
