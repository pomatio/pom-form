<?php

namespace PomatioFramework\Fields;

class Color {

	public static function render_field(array $args): void {

		echo '<div class="form-group">';

		if (!empty($args['label'])) {
			echo '<label for="' . $args['id'] . '">' . $args['label'] . '</label>';
		}

		if (!empty($args['description']) && $args['description_position'] === 'below_label') {
			echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
		}

        $value = '';
        if (isset($args['value']) && !empty($args['value'])) {
            $value = $args['value'];
        }
        elseif (isset($args['default']) && !empty($args['default'])) {
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
	}

}
