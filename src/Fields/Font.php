<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework;

class Font {

    public static function render_field(array $args): void {
        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . esc_attr($args['id']) . '">' . esc_html($args['label']) . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . wp_kses_post($args['description']) . '</small>';
        }

        echo (new Pomatio_Framework())::add_field([
            'type' => 'repeater',
            'title' => __('Fonts', 'pomatio-framework'),
            'description' => __('Add font families and their variants.', 'pomatio-framework'),
            'placeholder' => __('Font family', 'pomatio-framework'),
            'name' => $args['name'],
            'value' => $args['value'],
            'limit' => 5,
            'fields' => [
                [
                    'type' => 'Text',
                    'used_for_title' => true,
                    'label' => __('Font name', 'pomatio-framework'),
                    'description' => __('Used as the font-family name.', 'pomatio-framework'),
                    'name' => 'font_name',
                    'class' => 'regular-text',
                    'value' => '',
                ],
                [
                    'type' => 'Select',
                    'label' => __('Font type', 'pomatio-framework'),
                    'description' => __('Choose normal or variable font files.', 'pomatio-framework'),
                    'name' => 'font_type',
                    'options' => [
                        'normal' => __('Normal', 'pomatio-framework'),
                        'variable' => __('Variable', 'pomatio-framework'),
                    ],
                    'default' => 'normal',
                ],
                [
                    'type' => 'Select',
                    'label' => __('Font fallback', 'pomatio-framework'),
                    'description' => __('Fallback stack used when the custom font is unavailable.', 'pomatio-framework'),
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
                    'description' => __('Add variants with weight, style, and files.', 'pomatio-framework'),
                    'placeholder' => __('Variant', 'pomatio-framework'),
                    'name' => 'font_variant',
                    'limit' => 18,
                    'dependency' => [
                        [
                            [
                                'field' => 'font_type',
                                'values' => ['normal'],
                            ],
                        ],
                    ],
                    'fields' => [
                        [
                            'type' => 'Text',
                            'used_for_title' => true,
                            'label' => __('Variant name', 'pomatio-framework'),
                            'description' => __('Label for this variant (e.g., Regular 400).', 'pomatio-framework'),
                            'name' => 'variant_name',
                            'class' => 'regular-text',
                            'value' => '',
                        ],
                        [
                            'type' => 'Select',
                            'label' => __('Font weight', 'pomatio-framework'),
                            'description' => __('CSS font-weight for this variant.', 'pomatio-framework'),
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
                            'description' => __('CSS font-style for this variant.', 'pomatio-framework'),
                            'name' => 'font_style',
                            'options' => [
                                'normal' => __('Normal', 'pomatio-framework'),
                                'italic' => __('Italic', 'pomatio-framework'),
                                'oblique' => __('Oblique', 'pomatio-framework'),
                            ],
                        ],
                        [
                            'type' => 'Font_Picker',
                            'label' => __('Font files', 'pomatio-framework'),
                            'description' => __('Select font files for this variant.', 'pomatio-framework'),
                            'name' => 'font_variants',
                        ],
                    ]
                ],
                [
                    'type' => 'Font_Picker',
                    'label' => __('Variable font files', 'pomatio-framework'),
                    'description' => __('Upload WOFF2 and WOFF files for the variable font.', 'pomatio-framework'),
                    'name' => 'font_variable_files',
                    'allowed_extensions' => ['woff2', 'woff'],
                    'dependency' => [
                        [
                            [
                                'field' => 'font_type',
                                'values' => ['variable'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'Text',
                    'label' => __('Font weight range', 'pomatio-framework'),
                    'description' => __('Use two values, e.g. "100 900".', 'pomatio-framework'),
                    'placeholder' => '100 900',
                    'name' => 'font_variable_weight_range',
                    'class' => 'regular-text',
                    'dependency' => [
                        [
                            [
                                'field' => 'font_type',
                                'values' => ['variable'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'Text',
                    'label' => __('Font stretch range', 'pomatio-framework'),
                    'description' => __('Use two percentages, e.g. "75% 125%".', 'pomatio-framework'),
                    'placeholder' => '75% 125%',
                    'name' => 'font_variable_stretch_range',
                    'class' => 'regular-text',
                    'dependency' => [
                        [
                            [
                                'field' => 'font_type',
                                'values' => ['variable'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'Select',
                    'label' => __('Font style', 'pomatio-framework'),
                    'description' => __('CSS font-style for this variable font.', 'pomatio-framework'),
                    'name' => 'font_variable_style',
                    'options' => [
                        'normal' => __('Normal', 'pomatio-framework'),
                        'italic' => __('Italic', 'pomatio-framework'),
                        'oblique' => __('Oblique', 'pomatio-framework'),
                    ],
                    'default' => 'normal',
                    'dependency' => [
                        [
                            [
                                'field' => 'font_type',
                                'values' => ['variable'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'Select',
                    'label' => __('Font display', 'pomatio-framework'),
                    'description' => __('Controls font loading behavior.', 'pomatio-framework'),
                    'name' => 'font_variable_display',
                    'options' => [
                        'auto' => __('Auto', 'pomatio-framework'),
                        'block' => __('Block', 'pomatio-framework'),
                        'swap' => __('Swap', 'pomatio-framework'),
                        'fallback' => __('Fallback', 'pomatio-framework'),
                        'optional' => __('Optional', 'pomatio-framework'),
                    ],
                    'default' => 'swap',
                    'dependency' => [
                        [
                            [
                                'field' => 'font_type',
                                'values' => ['variable'],
                            ],
                        ],
                    ],
                ],
            ]
        ]);

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . wp_kses_post($args['description']) . '</small>';
        }

        echo '</div>';
    }

}
