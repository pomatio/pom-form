<?php

namespace POMFramework\Fields;

use POMFramework\POM_Framework;


// Meter input de imagen, con opción a que renderice 2 (normal y mobile por ejemplo)
class Background_Image {

    public static function render_field(array $args): void {
        ?>

        <div class="background-image-wrapper">
            <div class="image-wrapper">
                <?php

                echo (new POM_Framework())::add_field([
                    'label' => __('Background image', 'pom-framework'),
                    'type' => 'Image_Picker',
                    'name' => 'background-image',
                ]);

                ?>
            </div>

            <div class="horizontal-alignment">
                <?php

                echo (new POM_Framework())::add_field([
                    'label' => __('Horizontal alignment', 'pom-framework'),
                    'type' => 'Radio_Icons',
                    'name' => 'horizontal_alignment',
                    'options' => [
                        'left' => [
                            'label' => __('Left', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/horizontal-align-left.svg',
                        ],
                        'center' => [
                            'label' => __('Center', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/horizontal-align-center.svg',
                        ],
                        'right' => [
                            'label' => __('Right', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/horizontal-align-right.svg',
                        ],
                        'custom' => [
                            'label' => __('Custom', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/horizontal-align-custom.svg',
                        ],
                    ],
                    'default' => 'center'
                ]);

                ?>

                <div class="custom-horizontal_alignment-wrapper" style="display: none">
                    <?php

                    echo (new POM_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom', 'pom-framework'),
                        'name' => 'custom_horizontal_alignment_number'
                    ]);

                    echo (new POM_Framework())::add_field([
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

                echo (new POM_Framework())::add_field([
                    'label' => __('Vertical alignment', 'pom-framework'),
                    'type' => 'Radio_Icons',
                    'name' => 'vertical_alignment',
                    'options' => [
                        'top' => [
                            'label' => __('Top', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/vertical-align-top.svg',
                        ],
                        'center' => [
                            'label' => __('Center', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/vertical-align-center.svg',
                        ],
                        'bottom' => [
                            'label' => __('Bottom', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/vertical-align-down.svg',
                        ],
                        'custom' => [
                            'label' => __('Custom', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/vertical-align-custom.svg',
                        ],
                    ],
                    'default' => 'center'
                ]);

                ?>

                <div class="custom-vertical_alignment-wrapper" style="display: none">
                    <?php

                    echo (new POM_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom', 'pom-framework'),
                        'name' => 'custom_vertical_alignment_number'
                    ]);

                    echo (new POM_Framework())::add_field([
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

            echo (new POM_Framework())::add_field([
                'type' => 'Select',
                'label' => __('Background repeat', 'pom-framework'),
                'name' => 'background-repeat',
                'options' => [
                    'repeat' => __('Repeat', 'pom-framework'),
                    'repeat-x' => __('Repeat horizontally', 'pom-framework'),
                    'prepeat-y' => __('Repeat vertically', 'pom-framework'),
                    'no-repeat' => __('No repeat', 'pom-framework'),
                ],
                'default' => 'repeat'
            ]);

            echo (new POM_Framework())::add_field([
                'type' => 'Select',
                'label' => __('Background attachment', 'pom-framework'),
                'name' => 'background-attachment',
                'options' => [
                    'scroll' => __('Scroll', 'pom-framework'),
                    'fixed' => __('Fixed', 'pom-framework'),
                ],
                'default' => 'scroll'
            ]);

            ?>

            <div class="background-size-wrapper">
                <?php

                echo (new POM_Framework())::add_field([
                    'type' => 'Radio_Icons',
                    'label' => __('Background size', 'pom-framework'),
                    'name' => 'background-size',
                    'options' => [
                        'auto' => [
                            'label' => __('Auto', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/bg-size-auto.svg',
                        ],
                        'cover' => [
                            'label' => __('Cover', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/bg-size-cover.svg',
                        ],
                        'contain' => [
                            'label' => __('Contain', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/bg-size-contain.svg',
                        ],
                        'custom' => [
                            'label' => __('Custom', 'pom-framework'),
                            'icon' => POM_FRAMEWORK_SRC_PATH . '/dist/icons/bg-size-custom.svg',
                        ],
                    ],
                    'default' => 'auto'
                ]);

                ?>

                <div class="custom-background-size-wrapper" style="display: none">
                    <?php

                    echo (new POM_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom width', 'pom-framework'),
                        'name' => 'custom_background_size_width_number'
                    ]);

                    echo (new POM_Framework())::add_field([
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

                    echo (new POM_Framework())::add_field([
                        'type' => 'Number',
                        'placeholder' => __('Custom height', 'pom-framework'),
                        'name' => 'custom_background_size_height_number'
                    ]);

                    echo (new POM_Framework())::add_field([
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

        wp_enqueue_style('pom-framework-background-image', POM_FRAMEWORK_SRC_URI . '/dist/css/background-image.min.css');
        wp_enqueue_script('pom-framework-background-image',  POM_FRAMEWORK_SRC_URI . '/dist/js/background_image' . POM_FRAMEWORK_MIN . '.js', ['jquery'], null, true);
    }

}
