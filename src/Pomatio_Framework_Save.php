<?php
/**
 * Handle form submissions and sanitization.
 */

namespace PomatioFramework;

class Pomatio_Framework_Save {

    public static function save_settings($page_slug, $settings_file_path): void {
        if (!isset($_POST['save_pom_framework_fields'])) {
            return;
        }

        if (!isset($_POST['pom_framework_security_check']) || !wp_verify_nonce($_POST['pom_framework_security_check'], 'pom_framework_save_settings')) {
            return;
        }

        Pomatio_Framework_Disk::create_settings_dir($page_slug);

        $settings_path = (new Pomatio_Framework_Disk())->get_settings_path($page_slug);
        $tab = Pomatio_Framework_Settings::get_current_tab($settings_file_path);
        $subsection = Pomatio_Framework_Settings::get_current_subsection($settings_file_path);
        $settings_dirs = (new self)->get_current_tab_settings_dirs($settings_file_path, $tab, $subsection);

        require_once 'class-sanitize.php';

        $metadata = Pomatio_Framework_Disk::read_file('fields_save_as.php', $page_slug, 'array');
        $metadata = is_array($metadata) ? $metadata : [];

        foreach ($settings_dirs as $dir) {
            $data = [];
            $translatables = [];

            foreach ($_POST as $name => $value) {
                if (strpos($name, "{$dir}_") === 0) {
                    if ($name === "{$dir}_enabled") {
                        $setting_name = str_replace("{$dir}_", '', $name);
                        $data[$setting_name] = $value;
                        continue;
                    }

                    $field_definition = (new self)->get_field_definition($settings_file_path, $dir, $name);
                    $setting_name = str_replace(["{$dir}_", '[]'], '', $name);
                    $field_name = $field_definition['name'] ?? $setting_name;
                    $type = $field_definition['type'] ?? 'text';
                    $type = strtolower($type);
                    $sanitize_function_name = "sanitize_pom_form_$type";
                    $save_target = (new self)->normalize_save_target($field_definition['save_as'] ?? null);
                    $default_value = null;

                    if (is_array($field_definition) && array_key_exists('default', $field_definition)) {
                        $default_value = $field_definition['default'];
                    }

                    $persisted_in_alternative_storage = $save_target['type'] !== 'default';

                    if ($type === 'repeater') {
                        $sanitized_value = $sanitize_function_name($value, ['name' => $name], $page_slug);
                    }
                    elseif ($type === 'code_html' || $type === 'code_css' || $type === 'code_js' || $type === 'code_json' || $type === 'tinymce') {
                        $extension = $type === 'tinymce' ? 'html' : str_replace('code_', '', $type);
                        $translatable = (new self)->is_translatable($settings_file_path, $dir, $name) ?? false;

                        if ($persisted_in_alternative_storage) {
                            $raw_value = stripslashes($value);
                            $sanitized_value = function_exists($sanitize_function_name) ? $sanitize_function_name($raw_value) : $raw_value;
                        }
                        else {
                            $sanitized_value = Pomatio_Framework_Disk::save_to_file($name, stripslashes($value), $extension, $page_slug);
                        }

                        if ($translatable && $extension === 'html') {
                            $translatables[$field_name] = [
                                'filename' => $dir,
                                'multiline' => true,
                                'type' => $type
                            ];
                        }
                    }
                    else {
                        $sanitized_value = function_exists($sanitize_function_name) ? $sanitize_function_name($value) : $value;
                        $translatable = (new self)->is_translatable($settings_file_path, $dir, $name) ?? false;

                        if ($translatable && ($type === 'text' || $type === 'url' || $type === 'textarea' || $type === 'tinymce')) {
                            $multiline = $type === 'textarea' || $type === 'tinymce';
                            $translatables[$field_name] = [
                                'filename' => $dir,
                                'multiline' => $multiline,
                                'type' => $type
                            ];
                        }
                    }

                    if (!isset($sanitized_value)) {
                        $sanitized_value = $value;
                    }

                    if ($persisted_in_alternative_storage) {
                        if ($save_target['type'] === 'theme_mod') {
                            set_theme_mod($field_name, $sanitized_value);
                        }
                        elseif ($save_target['type'] === 'option') {
                            $autoload = $save_target['autoload'] ?? 'auto';
                            update_option($field_name, $sanitized_value, $autoload);
                        }

                        if (!isset($metadata[$dir])) {
                            $metadata[$dir] = [];
                        }

                        $metadata[$dir][$field_name] = [
                            'target' => $save_target['type'],
                            'autoload' => $save_target['autoload'] ?? null,
                            'default' => $default_value
                        ];
                    }
                    else {
                        $data[$setting_name] = $sanitized_value;

                        if (isset($metadata[$dir][$field_name])) {
                            unset($metadata[$dir][$field_name]);

                            if (empty($metadata[$dir])) {
                                unset($metadata[$dir]);
                            }
                        }
                    }

                    unset($sanitized_value);
                }
            }

            Pomatio_Framework_Translations::register($translatables, $page_slug);

            (new self)->save_settings_files($page_slug, $dir, $data);

            /**
             * "enabled_settings.php" has to be invalidated in each loop because otherwise it doesn't save well.
             */
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate("{$settings_path}enabled_settings.php", true);
                opcache_invalidate("$settings_path$dir.php", true);
            }
        }

        $metadata_content = (new Pomatio_Framework_Disk)->generate_file_content($metadata, 'Field storage metadata.');
        file_put_contents($settings_path . 'fields_save_as.php', $metadata_content, LOCK_EX);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($settings_path . 'fields_save_as.php', true);
        }

        do_action('pomatio_framework_after_save_settings', $page_slug, $tab, $subsection);
    }

    private function get_current_tab_settings_dirs($settings, $tab, $subsection): array {
        return array_keys($settings[$tab]['tab'][$subsection]['settings']);
    }

    private function save_settings_files($page_slug, $setting, $data): void {
        /**
         * Save the state of the setting.
         * 1 = Enabled
         * 0 = Disabled
         */
        $settings_array = array_filter((array)Pomatio_Framework_Disk::read_file('enabled_settings.php', $page_slug, 'array'));
        $settings_array[$setting] = isset($data['enabled']) && $data['enabled'] === 'yes' ? '1' : '0';

        $settings_content = (new Pomatio_Framework_Disk)->generate_file_content($settings_array, "Enabled settings array file.");
        $settings_path = (new Pomatio_Framework_Disk())->get_settings_path($page_slug);
        file_put_contents($settings_path . 'enabled_settings.php', $settings_content, LOCK_EX);

        // Don't save enabled/disabled option in tweak settings file.
        unset($data['enabled']);

        /**
         * Save settings to their specific file
         * only if setting is enabled. If disabled setting only
         * update setting status.
         */
        if ($settings_array[$setting] === '1' && count($data) > 0) {
            $setting_file_content = (new Pomatio_Framework_Disk)->generate_file_content($data, "Settings file for $setting.");
            file_put_contents($settings_path . "$setting.php", $setting_file_content, LOCK_EX);
        }
    }

    private function get_field_type($settings_array, string $setting_name, $field_name): ?string {
        $field = $this->get_field_definition($settings_array, $setting_name, $field_name);

        return is_array($field) && isset($field['type']) ? $field['type'] : null;
    }

    private function is_translatable($settings_array, string $setting_name, $field_name): bool {
        $field = $this->get_field_definition($settings_array, $setting_name, $field_name);

        return is_array($field) && isset($field['translatable']) && $field['translatable'] === true;
    }

    private function get_field_definition($settings_array, string $setting_name, $field_name): ?array {
        $field_slug = str_replace(["{$setting_name}_", '[]'], '', $field_name);
        $possible_dirs = [];

        if (
            isset($_GET['section'], $_GET['tab']) &&
            isset($settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir']) &&
            is_dir($settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir'])
        ) {
            $possible_dirs[] = $settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir'];
        }

        if (
            isset($_GET['section']) &&
            isset($settings_array[$_GET['section']]['settings_dir']) &&
            is_dir($settings_array[$_GET['section']]['settings_dir'])
        ) {
            $possible_dirs[] = $settings_array[$_GET['section']]['settings_dir'];
        }

        if (isset($settings_array['config']['settings_dir']) && is_dir($settings_array['config']['settings_dir'])) {
            $possible_dirs[] = $settings_array['config']['settings_dir'];
        }

        $possible_dirs = array_unique(array_filter($possible_dirs));

        foreach ($possible_dirs as $settings_dir) {
            $fields = Pomatio_Framework_Settings::read_fields($settings_dir, $setting_name);

            foreach ($fields as $field) {
                if (isset($field['name']) && $field['name'] === $field_slug) {
                    return $field;
                }
            }
        }

        return null;
    }

    private function normalize_save_target($save_as): array {
        $allowed_option_targets = [
            'option_autoload_yes' => 'yes',
            'option_autoload_no' => 'no',
            'option_autoload_auto' => 'auto'
        ];

        if (!is_string($save_as)) {
            return ['type' => 'default'];
        }

        $save_as = strtolower($save_as);

        if ($save_as === 'theme_mod') {
            return ['type' => 'theme_mod'];
        }

        if (isset($allowed_option_targets[$save_as])) {
            return [
                'type' => 'option',
                'autoload' => $allowed_option_targets[$save_as]
            ];
        }

        return ['type' => 'default'];
    }

}
