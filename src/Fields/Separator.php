<?php

namespace PomatioFramework\Fields;

class Separator {

    public static function render_field(array $args): void {
        if (isset($args['label']) && !empty($args['label'])) {
            echo "<h2 style='margin-bottom: 0;'>{$args['label']}</h2>";
        }

        if (isset($args['description']) && !empty($args['description'])) {
            echo '<p class="description form-text text-muted">' . $args['description'] . '</p>';
        }
    }

}
