<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Tinymce {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $custom_attrs = Pomatio_Framework_Helper::convert_html_attributes_to_array($args['custom_attrs']);

        $value = '';
        if (file_exists($args['value'])) {
            $value = file_get_contents($args['value']);
        }
        elseif (isset($args['default']) && !empty($args['default'])) {
            $value = $args['default'];
        }

        $tinymce_args = [
            'textarea_rows' => $custom_attrs['textarea_rows'] ?? 10,
            'teeny' => $custom_attrs['teeny'] ?? false,
            'quicktags' => $custom_attrs['quicktags'] ?? false,
            'wpautop' => $custom_attrs['wpautop'] ?? true,
            'media_buttons' => $custom_attrs['media_buttons'] ?? false
        ];
        wp_editor($value, $args['id'], $tinymce_args);

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

    }

}
