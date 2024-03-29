<?php

namespace PomatioFramework\Fields;

use PomatioFramework\Pomatio_Framework_Helper;

class Signature {

	public static function render_field(array $args): void {
		$disabled = isset($args['disabled']) && $args['disabled'] === true ? ' class="disabled"' : '';
        $button_class = $args['button_class'] ?? '';

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

		?>

        <div class="signature-pad-wrapper <?= $args['class'] ?? '' ?>">
            <div class="signature-pad">
                <canvas class="signature-canvas<?= $disabled ?>"></canvas>
                <br>
                <button class="signature-canvas-clear <?= $button_class ?>"><?php _e('Clear', 'pomatio-framework') ?></button>
                <input type="hidden" name="<?= $args['name'] ?>" class="signature-value" value="<?= $value ?>">
            </div>
        </div>

		<?php

		if (!empty($args['description']) && $args['description_position'] === 'under_field') {
			echo '<small class="description form-text text-muted">' . $args['description'] . '</small>';
		}

		echo '</div>';

		wp_enqueue_style('pomatio-framework-signature', POM_FORM_SRC_URI . '/dist/css/signature.min.css');
		wp_enqueue_script('pomatio-framework-signature', POM_FORM_SRC_URI . '/dist/js/signature' . POMATIO_MIN . '.js', [], null, true);
	}

}
