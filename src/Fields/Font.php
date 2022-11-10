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

        // font weight y font style y las variantes
        echo (new Pomatio_Framework())::add_field([
            'type' => 'repeater',
            'title' => __('Fonts', 'pomatio-framework'),
            'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'placeholder' => 'Lorem Ipsum',
            'name' => $args['name'],
            'value' => $args['value'],
            'limit' => 5,
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
                [
                    'type' => 'Select',
                    'label' => __('Font fallback', 'pomatio-framework'),
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
                    'name' => 'font_fallback',
                    'options' => [
                        'sans_serif' => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
                        'serif' => '"Times New Roman", Times, serif', // "Font 1 title", FALLBACK;
                        'monospace' => 'SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
                    ]
                ],
                [
                    'type' => 'repeater',
                    'label' => __('Font variants', 'pomatio-framework'),
                    'title' => __('Font variant', 'pomatio-framework'),
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
                    'placeholder' => 'Lorem Ipsum',
                    'name' => 'font_variant',
                    'limit' => 18,
                    'fields' => [
                        [
                            'type' => 'Text',
                            'used_for_title' => true,
                            'label' => __('Variant name', 'pomatio-framework'),
                            'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
                            'name' => 'variant_name',
                            'class' => 'regular-text',
                            'value' => '',
                        ],
                        [
                            'type' => 'Select',
                            'label' => __('Font weight', 'pomatio-framework'),
                            'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
                            'name' => 'font_weight',
                            'options' => [
                                'lighter' => __('100, lighter', 'pomatio-framework'),
                                '300' => __('300, light', 'pomatio-framework'),
                                '400' => __('400, normal', 'pomatio-framework'),
                                '600' => __('600, semibold', 'pomatio-framework'),
                                '700' => __('700, bold', 'pomatio-framework'),
                                'bolder' => __('900, bolder', 'pomatio-framework'),
                            ],
                        ],
                        [
                            'type' => 'Select',
                            'label' => __('Font style', 'pomatio-framework'),
                            'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
                            'name' => 'font_style',
                            'options' => [
                                'normal' => __('Normal', 'pomatio-framework'),
                                'italic' => __('Italic', 'pomatio-framework'),
                                'oblique' => __('Oblique', 'pomatio-framework'),
                            ],
                        ],
                        [
                            'type' => 'Font_Picker',
                            'label' => __('Font variant', 'pomatio-framework'),
                            'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing elit',
                            'name' => 'font_variants',
                        ],
                    ]
                ]
            ]
        ]);

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';
    }

}
