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

        $current_settings = Pomatio_Framework_Helper::get_settings($settings_file_path, $tab, $subsection);
        $fields_save_as_map = Pomatio_Framework_Disk::read_file('fields_save_as.php', $page_slug, 'array');
        $fields_save_as_map = is_array($fields_save_as_map) ? $fields_save_as_map : [];

        foreach ($settings_dirs as $dir) {
            $data = [];
            $translatables = [];
            $setting_definition = $current_settings[$dir] ?? [];
            $fields_metadata = isset($fields_save_as_map[$dir]) && is_array($fields_save_as_map[$dir]) ? $fields_save_as_map[$dir] : [];

            foreach ($_POST as $name => $value) {
                if (strpos($name, "{$dir}_") === 0) {
                    if ($name === "{$dir}_enabled") {
                        $setting_name = str_replace("{$dir}_", '', $name);
                        $data[$setting_name] = $value;
                        continue;
                    }

                    $setting_name = str_replace(["{$dir}_", '[]'], '', $name);
                    $setting_name = preg_replace('/\[.*$/', '', $setting_name);

                    $field_definition = (new self)->get_field_definition($settings_file_path, $dir, $name);
                    $field_definition = is_array($field_definition) ? $field_definition : [];
                    $type = $field_definition['type'] ?? (new self)->get_field_type($settings_file_path, $dir, $name) ?? 'text';
                    $type = strtolower($type);
                    $sanitize_function_name = "sanitize_pom_form_$type";
                    $translatable = isset($field_definition['translatable']) && $field_definition['translatable'] === true;
                    $save_target = (new self)->normalize_save_target($field_definition['save_as'] ?? '');

                    if ($type === 'repeater') {
                        $sanitized_value = $sanitize_function_name($value, ['name' => $name], $page_slug);

                        if ($save_target['storage'] === 'default') {
                            unset($fields_metadata[$setting_name]);
                            $data[$setting_name] = $sanitized_value;
                        }
                        else {
                            (new self)->persist_external_value($setting_name, $sanitized_value, $save_target, $fields_metadata, $field_definition);
                        }
                    }
                    elseif ($type === 'code_html' || $type === 'code_css' || $type === 'code_js' || $type === 'code_json' || $type === 'tinymce') {
                        $extension = $type === 'tinymce' ? 'html' : str_replace('code_', '', $type);
                        if ($save_target['storage'] === 'default') {
                            unset($fields_metadata[$setting_name]);
                            $data[$setting_name] = Pomatio_Framework_Disk::save_to_file($name, stripslashes($value), $extension, $page_slug);
                        }
                        else {
                            $sanitized_value = stripslashes($value);
                            if (function_exists($sanitize_function_name)) {
                                $sanitized_value = $sanitize_function_name($sanitized_value);
                            }

                            (new self)->persist_external_value($setting_name, $sanitized_value, $save_target, $fields_metadata, $field_definition);
                        }

                        if ($translatable && $extension === 'html') {
                            $translatables[$setting_name] = [
                                'filename' => $dir,
                                'multiline' => true,
                                'type' => $type
                            ];
                        }
                    }
                    else {
                        $sanitized = $sanitize_function_name($value);

                        if ($save_target['storage'] === 'default') {
                            unset($fields_metadata[$setting_name]);
                            $data[$setting_name] = $sanitized;
                        }
                        else {
                            (new self)->persist_external_value($setting_name, $sanitized, $save_target, $fields_metadata, $field_definition);
                        }

                        if ($translatable && ($type === 'text' || $type === 'url' || $type === 'textarea' || $type === 'tinymce')) {
                            $multiline = $type === 'textarea' || $type === 'tinymce';
                            $translatables[$setting_name] = [
                                'filename' => $dir,
                                'multiline' => $multiline,
                                'type' => $type
                            ];
                        }
                    }
                }
            }

            Pomatio_Framework_Translations::register($translatables, $page_slug);

            if (isset($setting_definition['requires_initialization']) && $setting_definition['requires_initialization'] === false) {
                $data['enabled'] = 'yes';
            }

            (new self)->save_settings_files($page_slug, $dir, $data);

            if (!empty($fields_metadata)) {
                ksort($fields_metadata);
                $fields_save_as_map[$dir] = $fields_metadata;
            }
            else {
                unset($fields_save_as_map[$dir]);
            }

            /**
             * "enabled_settings.php" has to be invalidated in each loop because otherwise it doesn't save well.
             */
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate("{$settings_path}enabled_settings.php", true);
                opcache_invalidate("$settings_path$dir.php", true);
            }
        }

        ksort($fields_save_as_map);
        $fields_metadata_content = (new Pomatio_Framework_Disk)->generate_file_content($fields_save_as_map, 'External storage map for Pomatio settings.');
        file_put_contents($settings_path . 'fields_save_as.php', $fields_metadata_content, LOCK_EX);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate("{$settings_path}fields_save_as.php", true);
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

        return $field['type'] ?? null;
    }

    private function get_field_definition($settings_array, string $setting_name, $field_name): ?array {
        $settings_dir = $this->resolve_settings_dir($settings_array);

        $fields = Pomatio_Framework_Settings::read_fields($settings_dir, $setting_name);
        $field_key = str_replace(["{$setting_name}_", '[]'], '', $field_name);
        $field_key = preg_replace('/\[.*$/', '', $field_key);

        foreach ($fields as $field) {
            if (!isset($field['name'])) {
                continue;
            }

            if ($field['name'] === $field_key) {
                return $field;
            }
        }

        return null;
    }

    private function resolve_settings_dir($settings_array): string {
        $settings_dir = '';

        if (
            isset($_GET['section'], $_GET['tab']) &&
            isset($settings_array[$_GET['section']]['tab']) &&
            is_array($settings_array[$_GET['section']]['tab']) &&
            isset($settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir']) &&
            is_dir($settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir'])
        ) {
            $settings_dir = $settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir'];
        }

        if (empty($settings_dir)) {
            if (isset($_GET['section'], $settings_array[$_GET['section']]['settings_dir']) && is_dir($settings_array[$_GET['section']]['settings_dir'])) {
                $settings_dir = $settings_array[$_GET['section']]['settings_dir'];
            }
            else {
                $settings_dir = $settings_array['config']['settings_dir'];
            }
        }

        return $settings_dir;
    }

    private function normalize_save_target($raw_value): array {
        $raw_value = is_string($raw_value) ? strtolower(trim($raw_value)) : '';

        switch ($raw_value) {
            case 'theme_mod':
                return [
                    'storage' => 'theme_mod',
                ];
            case 'option_autoload_yes':
                return [
                    'storage' => 'option',
                    'autoload' => 'yes',
                ];
            case 'option_autoload_no':
                return [
                    'storage' => 'option',
                    'autoload' => 'no',
                ];
            case 'option_autoload_auto':
                return [
                    'storage' => 'option',
                    'autoload' => 'auto',
                ];
            case '':
            case 'default':
                return [
                    'storage' => 'default',
                ];
        }

        return [
            'storage' => 'default',
        ];
    }

    private function persist_external_value(string $field_key, $value, array $save_target, array &$fields_metadata, array $field_definition): void {
        $default_value = $field_definition['default'] ?? '';

        if ($save_target['storage'] === 'theme_mod') {
            set_theme_mod($field_key, $value);

            $fields_metadata[$field_key] = [
                'storage' => 'theme_mod',
                'default' => $default_value,
            ];
        }
        elseif ($save_target['storage'] === 'option') {
            $autoload = $save_target['autoload'] ?? 'auto';

            if ($autoload === 'yes' || $autoload === 'no') {
                update_option($field_key, $value, $autoload);
            }
            else {
                update_option($field_key, $value);
            }

            $fields_metadata[$field_key] = [
                'storage' => 'option',
                'autoload' => $autoload,
                'default' => $default_value,
            ];
        }
    }

}
