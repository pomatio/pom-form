<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework;

class Background_Image {

    public static function render_field(array $args): void {
        ?>

        <div class="background-image-wrapper">
            <div class="horizontal-alignment">
                <?php

                echo (new Pomatio_Framework())::add_field([
                    'label' => __('Horizontal alignment', 'pomatio-framework'),
                    'type' => 'Radio',
                    'name' => 'horizontal_alignment',
                    'options' => [
                        'left' => __('Left', 'pomatio-framework'),
                        'center' => __('Center', 'pomatio-framework'),
                        'right' => __('Right', 'pomatio-framework'),
                        'custom' => __('Custom', 'pomatio-framework'),
                    ],
                    'default' => 'center'
                ]);

                ?>

                <div class="custom-horizontal-wrapper" style="display: none">
                    <?php

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Text',
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
                    'type' => 'Radio',
                    'name' => 'vertical_alignment',
                    'options' => [
                        'top' => __('Top', 'pomatio-framework'),
                        'center' => __('Center', 'pomatio-framework'),
                        'bottom' => __('Bottom', 'pomatio-framework'),
                        'custom' => __('Custom', 'pomatio-framework'),
                    ],
                    'default' => 'center'
                ]);

                ?>

                <div class="custom-vertical-wrapper" style="display: none">
                    <?php

                    echo (new Pomatio_Framework())::add_field([
                        'type' => 'Text',
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
                <div>
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
                            'type' => 'Select',
                            'label' => __('Background size', 'pomatio-framework'),
                            'name' => 'background_size',
                            'options' => [
                                'auto' => __('Auto', 'pomatio-framework'),
                                'cover' => __('Cover', 'pomatio-framework'),
                                'contain' => __('Contain', 'pomatio-framework'),
                                'custom' => __('Custom', 'pomatio-framework'),
                            ],
                            'default' => 'auto'
                        ]);

                        ?>
                        <div class="custom-background-size-wrapper" style="display: none">
                            <?php

                            echo (new Pomatio_Framework())::add_field([
                                'type' => 'Text',
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

                            echo (new Pomatio_Framework())::add_field([
                                'type' => 'Text',
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
                    <div>
                </div>
            </div>
        </div>

        <input type="hidden" name="<?= $args['name'] ?>" value="">

        <?php

        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }

        wp_enqueue_script('pomatio-framework-background-image',  POM_FORM_SRC_URI . '/dist/js/background_image' . POMATIO_MIN . '.js', ['jquery'], null, true);
    }

}
