<?php

namespace PomatioFramework\Fields;

class Color {

	public static function render_field(array $args): void {
		echo '<div class="form-group">';

		if (!empty($args['label'])) {
			echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label><br>';
		}

		if (!empty($args['description']) && $args['description_position'] === 'below_label') {
			echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
		}

        $value = '';
        if (!empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (!empty($args['default'])) {
            $value = $args['default'];
        }

        // Repeater integration
        $used_for_title = !empty($args['used_for_title']) ? ' use-for-title' : '';

		?>

		<input aria-label="<?= $args['label'] ?? '' ?>" type="text" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $value ?>" class="form-control pomatio-framework-color-picker<?= $used_for_title ?> <?= $args['class'] ?? '' ?>" data-default-color="#fff" data-type="color">

		<?php

		if (!empty($args['description']) && $args['description_position'] === 'under_field') {
			echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
		}

		echo '</div>';

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('pomatio-framework-color',  POM_FORM_SRC_URI . '/dist/js/color' . POMATIO_MIN . '.js', ['wp-color-picker'], null, true);
	}

}
