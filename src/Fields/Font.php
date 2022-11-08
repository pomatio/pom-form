<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework;

class Font {

    public static function render_field(array $args): void {
        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        $font_extensions = [
            'eot',
            'woff',
            'woff2',
            'ttf',
        ];

        echo (new Pomatio_Framework())::add_field([
            'type' => 'repeater',
            'title' => __('Fonts', 'pomatio-framework'),
            'label' => __('Fonts', 'pomatio-framework'),
            'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'placeholder' => 'Lorem Ipsum',
            'name' => $args['name'],
            'value' => '',
            'fields' => [
                [
                    'type' => 'Text',
                    'used_for_title' => true,
                    'label' => __('Font name', 'pomatio-framework'),
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
                    'name' => 'font_name',
                    'class' => 'regular-text',
                    'value' => '',
                ],
            ]
        ]);

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';
    }

}
