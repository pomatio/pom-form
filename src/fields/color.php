<?php

namespace PomatioFramework\fields;

class Color {

	public static function render_field(array $args): void {

		echo '<div class="form-group">';

		if (!empty($args['label'])) {
			echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
		}

		if (!empty($args['description']) && $args['description_position'] === 'below_label') {
			echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
		}

		?>

		<input aria-label="<?= $args['label'] ?? '' ?>" type="text" id="<?= $args['id'] ?>" name="<?= $args['name'] ?>" value="<?= $args['value'] ?>" class="form-control pom-form-color-picker <?= $args['class'] ?? '' ?>" data-default-color="#fff" data-type="color">

		<?php

		if (!empty($args['description']) && $args['description_position'] === 'under_field') {
			echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
		}

		echo '</div>';

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('pom-form-color',  POM_FORM_SRC_URI . '/dist/js/color.min.js', ['wp-color-picker'], null, true);

	}

}
