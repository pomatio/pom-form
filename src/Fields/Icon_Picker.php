<?php

namespace POMFramework\Fields;

use POMFramework\POM_Framework_Ajax;
use POMFramework\POM_Framework_Helper;

class Icon_Picker {

    public static function render_field(array $args): void {
        $class = !empty($args['class']) ? ' ' . $args[ 'class'] : '';
        $clearable = self::is_clearable($args);
        $wrapper_classes = ['icon-picker-wrapper'];

        if ($clearable) {
            $wrapper_classes[] = 'is-clearable';
        }

        if (!empty($args['value'])) {
            $wrapper_classes[] = 'has-selected-icon';
        }

        echo '<div class="form-group">';

        if (!empty($args['label'])) {
            echo '<label for="' . esc_attr($args['id']) . '">' . wp_kses_post($args['label']) . '</label><br>';
        }

        if (!empty($args['description']) && $args['description_position'] === 'below_label') {
            echo '<small class="description form-text text-muted">' . wp_kses_post($args['description']) . '</small>';
        }

        ?>

        <div class="<?= esc_attr(implode(' ', $wrapper_classes)) ?>">
            <?php if ($clearable) : ?>
                <span class="remove-selected-icon dashicons dashicons-no-alt" role="button" tabindex="0" aria-label="<?php esc_attr_e('Remove selected icon', 'pom-framework') ?>"></span>
            <?php endif; ?>
            <div class="icon-wrapper">
                <?php

                if (!empty($args['value'])) {
                    echo '<img alt="" src="' . esc_url($args['value']) . '">';
                }

                ?>
            </div>
            <span class="button open-icon-picker-modal"><?php _e('Select icon', 'pom-framework') ?></span>
            <input type="hidden" id="<?= esc_attr($args['id']) ?>" name="<?= esc_attr($args['name']) ?>" value="<?= esc_attr($args['value']) ?>" class="form-control<?= esc_attr($class) ?>" data-type="icon_picker">
        </div>

        <?php

        (new self())->dialog_html();

        if (!empty($args['description']) && $args['description_position'] === 'under_field') {
            echo '<small class="description form-text text-muted">' . wp_kses_post($args['description']) . '</small>';
        }

        echo '</div>';

        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('pom-framework-icon_picker', POM_FRAMEWORK_SRC_URI . '/dist/css/icon-picker.min.css', ['media-views', 'wp-jquery-ui-dialog']);
        wp_enqueue_script('pom-framework-icon_picker',  POM_FRAMEWORK_SRC_URI . '/dist/js/icon_picker' . POM_FRAMEWORK_MIN . '.js', ['jquery', 'jquery-ui-dialog'], null, true);
        wp_localize_script(
            'pom-framework-icon_picker',
            'pom_framework_icon_picker',
            [
                'loading' => __('Loading...', 'pom-framework'),
                'nonce' => wp_create_nonce(POM_Framework_Ajax::AJAX_NONCE_ACTION),
            ]
        );
    }

    public function dialog_html(): void {
        ?>

        <div id="pom-framework-icons-modal" class="media-modal wp-core-ui" style="display: none;">
            <button type="button" class="media-modal-close close-icon-picker-modal">
	                <span class="media-modal-icon">
	                    <span class="screen-reader-text"><?php esc_html_e('Close modal', 'pom-framework') ?></span>
	                </span>
            </button>
            <div class="media-modal-content" role="document">
                <div  class="media-frame mode-select wp-core-ui">
                    <div class="media-frame-title" id="media-frame-title"></div>
	                    <h2 class="media-frame-menu-heading"><?php esc_html_e('Icon libraries', 'pom-framework') ?></h2>
                    <div class="media-frame-menu">
                        <div role="tablist" aria-orientation="vertical" class="media-menu">
                            <?php

                            $icon_libraries = POM_Framework_Helper::get_icon_libraries();
                            foreach ($icon_libraries as $library => $data) {
                                ?>

	                                <button type="button" role="tab" class="media-menu-item" id="menu-item-<?= esc_attr($library) ?>" data-slug="<?= esc_attr($library) ?>" data-label="<?= esc_attr($data['name']) ?>"><?= esc_html($data['name']) ?></button>

                                <?php
                            }

                            ?>
                        </div>
                    </div>
                    <div class="media-frame-tab-panel">
                        <div class="media-frame-router">
                            <div role="tablist" aria-orientation="horizontal" class="media-router">
	                                <input placeholder="<?php esc_attr_e('Search icon', 'pom-framework') ?>" aria-label="<?php esc_attr_e('Search icon', 'pom-framework') ?>" id="icon-search" type="search">
                            </div>
                        </div>
                        <div class="media-frame-content">
	                            <span class="centered-text"><?php esc_html_e('Loading...', 'pom-framework') ?></span>
                        </div>
                    </div>
                    <div class="media-frame-toolbar">
                        <div class="media-toolbar">
                            <div class="media-toolbar-primary">
	                                <button class="button media-button button-primary button-large media-button-select pom-framework-icon-select-button disabled" disabled><?php esc_html_e('Select icon', 'pom-framework') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }

    private static function is_clearable(array $args): bool {
        $value = $args['clearable'] ?? $args['allow_clear'] ?? true;

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return !empty($value);
    }

}
