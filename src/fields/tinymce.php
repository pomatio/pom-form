<?php

namespace PomatioFramework\fields;

class Tinymce {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $custom_attrs = POM_Form_Helper::convert_html_attributes_to_array($args['custom_attrs']);

        $tinymce_args = [
            'textarea_rows' => $custom_attrs['textarea_rows'] ?? 10,
            'teeny' => $custom_attrs['teeny'] ?? false,
            'quicktags' => $custom_attrs['quicktags'] ?? false,
            'wpautop' => $custom_attrs['wpautop'] ?? true,
            'media_buttons' => $custom_attrs['media_buttons'] ?? false
        ];
        wp_editor($args['value'], $args['id'], $tinymce_args);

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

    }

}
