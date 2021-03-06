<?php
/**
 * Handle form submissions and sanitization.
 */

namespace POM\Form;

class POMATIO_Framework_Save {

    public static function save_settings($page_slug, $settings_file_path): void {
        if (!isset($_POST['save_pom_framework_fields']) ) {
            return;
        }

        if (!isset($_POST['pom_framework_security_check']) || !wp_verify_nonce($_POST['pom_framework_security_check'], 'pom_framework_save_settings')) {
            return;
        }

        POM_Form_Disk::create_settings_dir($page_slug);

        $settings_path = (new POM_Form_Disk())->get_settings_path($page_slug);
        $settings_dirs = (new self)->get_current_tab_settings_dirs($settings_file_path, POM_Framework_Settings::get_current_tab($settings_file_path), POM_Framework_Settings::get_current_subsection($settings_file_path));

        foreach ($settings_dirs as $dir) {
            $data = [];

            foreach ($_POST as $name => $value) {
                if (strpos($name, "{$dir}_") === 0) {
                    if ($name === "{$dir}_enabled") {
                        $setting_name = str_replace("{$dir}_", '', $name);
                        $data[$setting_name] = $value;
                        continue;
                    }

                    $setting_name = str_replace("{$dir}_", '', $name);

                    $type = (new self)->get_field_type($settings_file_path, $dir, $name) ?? 'text';
                    $sanitize_function_name = "sanitize_pom_form_{$type}";

                    if ($type === 'repeater') {
                        $data[$setting_name] = $sanitize_function_name($value, ['name' => $name], $page_slug);
                    }
                    elseif ($type === 'code_html' || $type === 'code_css' || $type === 'code_js') {
                        $data[$setting_name] = POM_Form_Disk::save_to_file($name, $value, str_replace('code_', '', $type), $page_slug);
                    }
                    else {
                        $data[$setting_name] = $sanitize_function_name($value);
                    }
                }
            }

            (new self)->save_settings_files($page_slug, $dir, $data);

            /**
             * "enabled_settings.php" has to be invalidated in each loop because otherwise it doesn't save well.
             */
            opcache_invalidate("{$settings_path}enabled_settings.php", true);
            opcache_invalidate("{$settings_path}{$dir}.php", true);
        }
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
        $settings_array = array_filter((array)POM_Form_Disk::read_file('enabled_settings.php', $page_slug, 'array'));
        $settings_array[$setting] = isset($data['enabled']) && $data['enabled'] === 'yes' ? '1' : '0';

        $settings_content = (new POM_Form_Disk)->generate_file_content($settings_array, "Enabled settings array file.");
        $settings_path = (new POM_Form_Disk())->get_settings_path($page_slug);
        file_put_contents($settings_path . 'enabled_settings.php', $settings_content, LOCK_EX);

        // Don't save enabled/disabled option in tweak settings file.
        unset($data['enabled']);

        /**
         * Save settings to their specific file
         * only if setting is enabled. If disabled setting only
         * update setting status.
         */
        if ($settings_array[$setting] === '1' && count($data) > 0) {
            $setting_file_content = (new POM_Form_Disk)->generate_file_content($data, "Settings file for $setting.");
            file_put_contents($settings_path . "$setting.php", $setting_file_content, LOCK_EX);
        }
	}

    /**
     * Gets the index of the child array
     * within a multidimensional array.
     *
     * @param $settings_array
     * @param string $setting_name Fields name as is stored in file. Without tweak name prefix.
     * @param $field_name
     *
     * @return string|null
     */
    private function get_field_type($settings_array, string $setting_name, $field_name): ?string {
        $fields = POM_Framework_Settings::read_fields($settings_array['config']['settings_dir'], $setting_name);

        foreach ($fields as $field) {

            if ($field['name'] === str_replace("{$setting_name}_", '', $field_name)) {
                return $field['type'];
            }
        }

        return null;
    }

}
