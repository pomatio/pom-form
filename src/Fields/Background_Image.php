<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework;


// Meter input de imagen, con opción a que renderice 2 (normal y mobile por ejemplo)
class Background_Image {

    public static function render_field(array $args): void {
        ?>

        <div class="background-image-wrapper">
            <div class="image-wrapper">
                <?php

                echo (new Pomatio_Framework())::add_field([
                    'label' => __('Background image', 'pomatio-framework'),
                    'type' => 'Image_Picker',
                    'name' => 'background_image',
                ]);

                ?>
            </div>

            <div class="horizontal-alignment">
                <?php

                echo (new Pomatio_Framework())::add_field([
                    'label' => __('Horizontal alignment', 'pomatio-framework'),
                    'type' => 'Radio_Icons',
                    'name' => 'horizontal_alignment',
                    'options' => [
                        'left' => [
                            'label' => __('Left', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-100.svg',
                        ],
                        'center' => [
                            'label' => __('Center', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-300.svg',
                        ],
                        'right' => [
                            'label' => __('Right', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-600.svg',
                        ],
                        'custom' => [
                            'label' => __('Custom', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-900.svg',
                        ],
                    ],
                    'default' => 'center'
                ]);

                ?>

                <div class="custom-horizontal_alignment-wrapper" style="display: none">
                    <?php

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom', 'pomatio-framework'),
                        'name' => 'custom_horizontal_alignment_number'
                    ]);

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Select',
                        'name' => 'custom_horizontal_alignment_unit',
                        'options' => [
                            '%' => '%',
                            'px' => 'px',
                            'rem' => 'rem',
                            'em' => 'em',
                        ],
                        'default' => 'px'
                    ]);

                    ?>
                </div>
            </div>
            <div class="vertical-alignment">
                <?php

                echo (new Pomatio_Framework())::add_field([
                    'label' => __('Vertical alignment', 'pomatio-framework'),
                    'type' => 'Radio_Icons',
                    'name' => 'vertical_alignment',
                    'options' => [
                        'top' => [
                            'label' => __('Top', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-100.svg',
                        ],
                        'center' => [
                            'label' => __('Center', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-300.svg',
                        ],
                        'bottom' => [
                            'label' => __('Bottom', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-600.svg',
                        ],
                        'custom' => [
                            'label' => __('Custom', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-900.svg',
                        ],
                    ],
                    'default' => 'center'
                ]);

                ?>

                <div class="custom-vertical_alignment-wrapper" style="display: none">
                    <?php

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom', 'pomatio-framework'),
                        'name' => 'custom_vertical_alignment_number'
                    ]);

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Select',
                        'name' => 'custom_vertical_alignment_unit',
                        'options' => [
                            '%' => '%',
                            'px' => 'px',
                            'rem' => 'rem',
                            'em' => 'em',
                        ],
                        'default' => 'px'
                    ]);

                    ?>
                </div>
            </div>
            <?php

            echo (new Pomatio_Framework())::add_field([
                'type' => 'Select',
                'label' => __('Background position', 'pomatio-framework'),
                'name' => 'background_position',
                'options' => [
                    'repeat' => __('Repeat', 'pomatio-framework'),
                    'repeat-x' => __('Repeat horizontally', 'pomatio-framework'),
                    'prepeat-y' => __('Repeat vertically', 'pomatio-framework'),
                    'no-repeat' => __('No repeat', 'pomatio-framework'),
                ],
                'default' => 'repeat'
            ]);

            echo (new Pomatio_Framework())::add_field([
                'type' => 'Select',
                'label' => __('Background attachment', 'pomatio-framework'),
                'name' => 'background_attachment',
                'options' => [
                    'scroll' => __('Scroll', 'pomatio-framework'),
                    'fixed' => __('Fixed', 'pomatio-framework'),
                ],
                'default' => 'scroll'
            ]);

            ?>

            <div class="background-size-wrapper">
                <?php

                echo (new Pomatio_Framework())::add_field([
                    'type' => 'Radio_Icons',
                    'label' => __('Background size', 'pomatio-framework'),
                    'name' => 'background_size',
                    'options' => [
                        'auto' => [
                            'label' => __('Auto', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-100.svg',
                        ],
                        'cover' => [
                            'label' => __('Cover', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-300.svg',
                        ],
                        'contain' => [
                            'label' => __('Contain', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-600.svg',
                        ],
                        'custom' => [
                            'label' => __('Custom', 'pomatio-framework'),
                            'icon' => POM_FORM_SRC_PATH . '/dist/icons/font-weight-900.svg',
                        ],
                    ],
                    'default' => 'auto'
                ]);

                ?>

                <div class="custom-background-size-wrapper" style="display: none">
                    <?php

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom width', 'pomatio-framework'),
                        'name' => 'custom_background_size_width_number'
                    ]);

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Select',
                        'name' => 'custom_background_size_width_unit',
                        'options' => [
                            '%' => '%',
                            'px' => 'px',
                            'rem' => 'rem',
                            'em' => 'em',
                        ],
                        'default' => 'px'
                    ]);

                    echo ' x ';

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom height', 'pomatio-framework'),
                        'name' => 'custom_background_size_height_number'
                    ]);

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Select',
                        'name' => 'custom_background_size_height_unit',
                        'options' => [
                            '%' => '%',
                            'px' => 'px',
                            'rem' => 'rem',
                            'em' => 'em',
                        ],
                        'default' => 'px'
                    ]);

                    ?>
                </div>
            </div>

            <input type="hidden" name="<?= $args['name'] ?>" value="<?= htmlspecialchars(json_encode($args['value'])) ?>" data-type="background_image">
        </div>

        <?php

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }

        wp_enqueue_style('pomatio-framework-background-image', POM_FORM_SRC_URI . '/dist/css/background-image.min.css');
        wp_enqueue_script('pomatio-framework-background-image',  POM_FORM_SRC_URI . '/dist/js/background_image' . POMATIO_MIN . '.js', ['jquery'], null, true);
    }

}
