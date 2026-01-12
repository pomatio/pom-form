<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Font_Picker {

    public static function render_field(array $args): void {
        $allowed_font_types = Pomatio_Framework_Helper::get_allowed_font_types();
        $default_extensions = array_keys($allowed_font_types);
        $font_extensions = $args['allowed_extensions'] ?? $default_extensions;
        if (!is_array($font_extensions)) {
            $font_extensions = $default_extensions;
        }
        $font_extensions = array_values(array_intersect($font_extensions, $default_extensions));
        if (empty($font_extensions)) {
            $font_extensions = $default_extensions;
        }
        $font_labels = [
            'woff2' => __('WOFF2 (.woff2)', 'pomatio-framework'),
            'woff' => __('WOFF (.woff)', 'pomatio-framework'),
            'ttf' => __('TTF (.ttf)', 'pomatio-framework'),
        ];
        $extra_mime_types = [
            'woff2' => [
                'application/font-woff2',
            ],
            'woff' => [
                'application/x-font-woff',
                'application/font-woff',
            ],
            'ttf' => [
                'application/x-font-ttf',
                'application/x-font-truetype',
            ],
        ];
        $build_mime_types = static function (array $extensions) use ($allowed_font_types, $extra_mime_types): array {
            $mime_types = [];
            foreach ($extensions as $extension) {
                if (isset($allowed_font_types[$extension])) {
                    $mime_types[] = $allowed_font_types[$extension];
                }
                if (isset($extra_mime_types[$extension])) {
                    $mime_types = array_merge($mime_types, $extra_mime_types[$extension]);
                }
            }

            return array_values(array_unique($mime_types));
        };
        $mime_types = $build_mime_types($font_extensions);
        $global_mime_types = $build_mime_types($default_extensions);
        $data_dependencies = Pomatio_Framework_Helper::get_dependencies_data_attr($args);

        if (is_string($args['value'])) {
            $args['value'] = str_replace('&quot;', '"', $args['value']);
            $args['value'] = json_decode($args['value'], true);
        }

        ?>

        <div class="font-variants-wrapper form-group"<?= $data_dependencies ?>>
            <?php

            foreach ($font_extensions as $extension) {
                $id = Pomatio_Framework_Helper::generate_random_string(10, false);
                $value = !empty($args['value'][$extension]) ? $args['value'][$extension] : '';
                $label = $font_labels[$extension] ?? strtoupper($extension);
                $mime_types_attr = esc_attr(wp_json_encode($mime_types));

                ?>

                <div class="font-variant <?= esc_attr($extension) ?>" style="margin-bottom: 10px;">
                    <label for="<?= esc_attr($id . '-' . $extension) ?>" style="display: block;"><?= esc_html($label) ?></label>
                    <input id="<?= esc_attr($id . '-' . $extension) ?>" type="url" name="<?= esc_attr("{$args['name']}[$extension]") ?>" value="<?= esc_url($value) ?>" data-type="font_picker">
                    <span class="button open-font-picker" data-mime-types="<?= $mime_types_attr ?>"><?php _e('Select font file', 'pomatio-framework') ?></span>
                </div>

                <?php
            }

            ?>
        </div>

        <?php

        wp_enqueue_media();
        wp_enqueue_style('pomatio-framework-font_picker', POM_FORM_SRC_URI . '/dist/css/font-picker.min.css');
        wp_enqueue_script('pomatio-framework-font_picker',  POM_FORM_SRC_URI . '/dist/js/font_picker' . POMATIO_MIN . '.js', ['jquery'], null, true);
        wp_localize_script(
            'pomatio-framework-font_picker',
            'pom_form_font_picker',
            [
                'title' => __('Choose font', 'pomatio-framework'),
                'button' => __('Choose font', 'pomatio-framework'),
                'mime_types' => $global_mime_types,
            ]
        );
    }

}
