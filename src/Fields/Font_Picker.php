<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Font_Picker {

    public static function render_field(array $args): void {
        $font_extensions = array_keys(Pomatio_Framework_Helper::get_allowed_font_types());
        $font_labels = [
            'eot' => __('EOT (.eot)', 'pomatio-framework'),
            'otf' => __('OTF (.otf)', 'pomatio-framework'),
            'woff' => __('WOFF (.woff)', 'pomatio-framework'),
            'woff2' => __('WOFF2 (.woff2)', 'pomatio-framework'),
            'ttf' => __('TTF (.ttf)', 'pomatio-framework'),
        ];
        $mime_types = array_values(Pomatio_Framework_Helper::get_allowed_font_types());
        $mime_types = array_merge(
            $mime_types,
            [
                'application/x-font-otf',
                'application/x-font-ttf',
                'application/x-font-woff',
                'application/font-woff',
                'application/font-woff2',
                'application/font-sfnt',
                'application/x-font-opentype',
                'application/x-font-truetype',
            ]
        );
        $mime_types = array_values(array_unique($mime_types));

        if (is_string($args['value'])) {
            $args['value'] = str_replace('&quot;', '"', $args['value']);
            $args['value'] = json_decode($args['value'], true);
        }

        ?>

        <div class="font-variants-wrapper">
            <?php

            foreach ($font_extensions as $extension) {
                $id = Pomatio_Framework_Helper::generate_random_string(10, false);
                $value = !empty($args['value'][$extension]) ? $args['value'][$extension] : '';
                $label = $font_labels[$extension] ?? strtoupper($extension);

                ?>

                <div class="font-variant <?= esc_attr($extension) ?>" style="margin-bottom: 10px;">
                    <label for="<?= esc_attr($id . '-' . $extension) ?>" style="display: block;"><?= esc_html($label) ?></label>
                    <input id="<?= esc_attr($id . '-' . $extension) ?>" type="url" name="<?= esc_attr("{$args['name']}[$extension]") ?>" value="<?= esc_url($value) ?>" data-type="font_picker">
                    <span class="button open-font-picker"><?php _e('Select font file', 'pomatio-framework') ?></span>
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
                'mime_types' => $mime_types,
            ]
        );
    }

}
