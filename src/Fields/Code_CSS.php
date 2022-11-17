<?php

namespace PomatioFramework\Fields;

class Code_CSS {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        if (file_exists($args['value'])) {
            $args['value'] = file_get_contents($args['value']);
        }

        ?>

        <textarea aria-label="<?= $args['label'] ?>" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" class="form-control pomatio-framework-code-editor-css <?= $args['class'] ?>" data-type="code_css"><?= $args['value'] ?></textarea>

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
