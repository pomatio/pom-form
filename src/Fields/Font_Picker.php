<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Font_Picker {

    public static function render_field(array $args): void {
        $font_extensions = Pomatio_Framework_Helper::get_allowed_font_types();

        if (is_string($args['value'])) {
            $args['value'] = str_replace('&quot;', '"', $args['value']);
            $args['value'] = json_decode($args['value'], true);
        }

        ?>

        <div class="font-variants-wrapper">
            <?php

            foreach ($font_extensions as $extension => $extension_label) {
                $id = Pomatio_Framework_Helper::generate_random_string(10, false);
                $value = isset($args['value'][$extension]) && !empty($args['value'][$extension]) ? $args['value'][$extension] : '';

                ?>

                <div class="font-variant <?= $extension ?>" style="margin-bottom: 10px;">
                    <label for="<?= $id . '-' . $extension ?>" style="display: block;"><?= $extension_label ?></label>
                    <input id="<?= $id . '-' . $extension ?>" type="url" name="<?= "{$args['name']}[$extension]" ?>" value="<?= $value ?>" data-type="font_picker">
                    <button class="button open-font-picker"><?php _e('Select font file', 'pomatio-framework') ?></button>
                </div>

                <?php
            }

            ?>
        </div>

        <?php

        wp_enqueue_style('pomatio-framework-font_picker', POM_FORM_SRC_URI . '/dist/css/font-picker' . POMATIO_MIN . '.css');
        wp_enqueue_script('pomatio-framework-font_picker',  POM_FORM_SRC_URI . '/dist/js/font_picker' . POMATIO_MIN . '.js', ['jquery'], null, true);
        wp_localize_script(
            'pomatio-framework-font_picker',
            'pom_form_font_picker',
            [
                'title' => __('Choose font', 'pomatio-framework'),
                'button' => __('Choose font', 'pomatio-framework'),
            ]
        );
    }

}
