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
		$settings_dirs = (new self)->get_current_tab_settings_dirs($settings_file_path, Pomatio_Framework_Settings::get_current_tab($settings_file_path), Pomatio_Framework_Settings::get_current_subsection($settings_file_path));
		
		require_once 'class-sanitize.php';
		
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
					
					$setting_name = str_replace(["{$dir}_", '[]'], '', $name);
					$type = (new self)->get_field_type($settings_file_path, $dir, $name) ?? 'text';
					$type = strtolower($type);
					$sanitize_function_name = "sanitize_pom_form_$type";
					
					if ($type === 'repeater') {
						$data[$setting_name] = $sanitize_function_name($value, ['name' => $name], $page_slug);
					}
					elseif ($type === 'code_html' || $type === 'code_css' || $type === 'code_js' || $type === 'code_json' || $type === 'tinymce') {
						$extension = $type === 'tinymce' ? 'html' : str_replace('code_', '', $type);
						$data[$setting_name] = Pomatio_Framework_Disk::save_to_file($name, stripslashes($value), $extension, $page_slug);
						$translatable = (new self)->is_translatable($settings_file_path, $dir, $name) ?? false;
						
						if ($translatable && $type === 'tinymce') {
							$translatables[$setting_name] = [
								'filename' => $dir,
								'multiline' => true,
								'type' => $type
							];
						}
					}
					else {
						$sanitized = $sanitize_function_name($value);
						$data[$setting_name] = $sanitized;
						$translatable = (new self)->is_translatable($settings_file_path, $dir, $name) ?? false;
						
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
			
			(new self)->save_settings_files($page_slug, $dir, $data);
			
			/**
			 * "enabled_settings.php" has to be invalidated in each loop because otherwise it doesn't save well.
			 */
			if (function_exists('opcache_invalidate')) {
				opcache_invalidate("{$settings_path}enabled_settings.php", true);
				opcache_invalidate("$settings_path$dir.php", true);
			}
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
		$fields = Pomatio_Framework_Settings::read_fields($settings_array['config']['settings_dir'], $setting_name);
		
		foreach ($fields as $field) {
			if ($field['name'] === str_replace("{$setting_name}_", '', $field_name)) {
				return $field['type'];
			}
		}
		
		return null;
	}
	
	private function is_translatable($settings_array, string $setting_name, $field_name): bool {
		// Check $settings_array[$_GET['section']]['settings_dir'] for plugins.
		$settings_dir = isset($settings_array[$_GET['section']]['settings_dir']) && is_dir($settings_array[$_GET['section']]['settings_dir']) ? $settings_array[$_GET['section']]['settings_dir'] : $settings_array['config']['settings_dir'];
		
		$fields = Pomatio_Framework_Settings::read_fields($settings_dir, $setting_name);
		
		foreach ($fields as $field) {
			if ($field['name'] === str_replace("{$setting_name}_", '', $field_name)) {
				return isset($field['translatable']) && $field['translatable'] === true;
			}
		}
		
		return false;
	}
	
}
