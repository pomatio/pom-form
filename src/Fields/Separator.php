<?php

namespace PomatioFramework\Fields;

class Separator {

    public static function render_field(array $args): void {
        if (!empty($args['label'])) {
            echo "<h2 class='title'>{$args['label']}</h2>";
        }

        if (!empty($args['description'])) {
            echo "<p>{$args['description']}</p>";
        }
    }

}
