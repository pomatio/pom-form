<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Icon_Picker {

    public static function render_field(array $args): void {

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        ?>

        <div class="icon-picker-wrapper">
            <span class="remove-selected-icon dashicons dashicons-trash"></span>
            <div class="icon-wrapper">
                <?php

                if (!empty($args['value'])) {
                    echo '<img alt="" src="' . $args['value'] . '">';
                }

                ?>
            </div>
            <span class="button open-icon-picker-modal"><?php _e('Select icon', 'pomatio-framework') ?></span>
            <input type="hidden" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-control <?= $args['class'] ?? '' ?>">
        </div>

        <?php

        (new self())->dialog_html();

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('pomatio-framework-icon_picker', POM_FORM_SRC_URI . '/dist/css/icon-picker.min.css');
        wp_enqueue_script('pomatio-framework-icon_picker',  POM_FORM_SRC_URI . '/dist/js/icon_picker' . POMATIO_MIN . '.js', ['jquery'], null, true);
        wp_localize_script(
            'pomatio-framework-icon_picker',
            'pom_form_icon_picker',
            [
                'loading' => __('Loading...', 'pomatio-framework')
            ]
        );
    }

    private function dialog_html(): void {
        ?>

        <div id="pomatio-framework-icons-modal" class="media-modal wp-core-ui" style="display: none;">
            <button type="button" class="media-modal-close close-icon-picker-modal">
                <span class="media-modal-icon">
                    <span class="screen-reader-text"><?php _e('Close modal', 'pomatio-framework') ?></span>
                </span>
            </button>
            <div class="media-modal-content" role="document">
                <div  class="media-frame mode-select wp-core-ui">
                    <div class="media-frame-title" id="media-frame-title"></div>
                    <h2 class="media-frame-menu-heading"><?php _e('Icon libraries', 'pomatio-framework') ?></h2>
                    <div class="media-frame-menu">
                        <div role="tablist" aria-orientation="vertical" class="media-menu">
                            <?php

                            $icon_libraries = Pomatio_Framework_Helper::get_icon_libraries();
                            foreach ($icon_libraries as $library => $data) {
                                ?>

                                <button type="button" role="tab" class="media-menu-item" id="menu-item-<?= $library ?>" data-slug="<?= $library ?>" data-label="<?= $data['name'] ?>"><?= $data['name'] ?></button>

                                <?php
                            }

                            ?>
                        </div>
                    </div>
                    <div class="media-frame-tab-panel">
                        <div class="media-frame-router">
                            <div role="tablist" aria-orientation="horizontal" class="media-router">
                                <input placeholder="<?php _e('Search icon', 'pomatio-framework') ?>" aria-label="<?php _e('Search icon', 'pomatio-framework') ?>" id="icon-search" type="search">
                            </div>
                        </div>
                        <div class="media-frame-content">
                            <span class="centered-text"><?php _e('Loading...', 'pomatio-framework') ?></span>
                        </div>
                    </div>
                    <div class="media-frame-toolbar">
                        <div class="media-toolbar">
                            <div class="media-toolbar-primary">
                                <button class="button media-button button-primary button-large media-button-select pomatio-framework-icon-select-button disabled" disabled><?php _e('Select icon', 'pomatio-framework') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

}
