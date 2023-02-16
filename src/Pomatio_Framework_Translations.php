<?php

namespace PomatioFramework;

class Pomatio_Framework_Translations {
    /**
     * TODO: When a field that has already been translatable is no longer translatable, it does not disappear from the translatable strings.
     */

    public function __construct() {
        add_action('admin_init', [$this, 'register_translatable_strings']);
    }

    public function register_translatable_strings(): void {
        if (!function_exists('pll_register_string')) {
            return;
        }

        $settings_path = (new Pomatio_Framework_Disk)->get_settings_path('pom-theme-options');

        $strings = include "{$settings_path}translatable_strings.php";

        if (!empty($strings)) {
            foreach ($strings as $name => $data) {
                pll_register_string($name, $data['string'], 'Pomatio Framework', $data['multiline']);
            }
        }
    }

    /**
     * Saves in a file all the information needed to register the translatable strings.
     */
    public static function register($setting_name, $string, $multiline, $settings_dir): void {
        $strings = Pomatio_Framework_Disk::read_file('translatable_strings.php', $settings_dir, 'array');

        $strings = empty($strings) ? [] : $strings;

        $strings[$setting_name] = [
            'string' => $string,
            'multiline' => $multiline,
        ];

        $content = (new Pomatio_Framework_Disk())->generate_file_content($strings, 'String translations.');
        Pomatio_Framework_Disk::save_to_file('translatable_strings', $content, 'php', $settings_dir);
    }


    /**
     * If Polylang is active, translate a previously registered string.
     *
     * @param $string
     *
     * @return string
     */
    public static function translate($string): string {
        return function_exists('pll__') ? pll__($string) : $string;
    }

}
new Pomatio_Framework_Translations();
