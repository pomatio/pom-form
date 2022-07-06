<?php
/**
 * Class used for handling disk-related operations.
 */

namespace POM\Form;

class POM_Form_Disk {

    /**
     * Current site data.
     */
    private $site_data;

    /**
     * Current site settings files path.
     *
     * @var string
     */
    public string $settings_path;

    public function __construct() {
        $this->site_data = get_blog_details();

        $multisite_path = '';
        if (is_multisite()) {
            $multisite_path = "sites/{$this->site_data->blog_id}/";
        }

        /**
         * Filter to set the name under which the settings are saved.
         */
        $settings_dir = apply_filters('pom_form_settings_dir', 'pom-form');

        $this->settings_path = WP_CONTENT_DIR . "/settings/$settings_dir/$multisite_path";
    }

    /**
     * Creates the directory in which the configuration files are saved.
     * If a multisite, it is subdivided into /sites/blog_id/.
     *
     * @return void
     */
    private function create_settings_dir(): void {
        if (!is_dir($this->settings_path)) {
            $created = wp_mkdir_p($this->settings_path);
            if (!$created) {
                POM_Form_Helper::write_log('Error creating tweaks settings dir.');
            }
        }
    }

    /**
     * Generates the file content by converting the data to an array.
     *
     * @param $data
     * @param string $description
     *
     * @return string
     */
    private function generate_file_content($data, string $description = ''): string {
        if (empty($data)) {
            return '';
        }

        $file_content  = '<?php' . PHP_EOL;
        $file_content .= '/**' . PHP_EOL;

        if (!empty($description)) {
            $file_content .= ' * ' . $description . PHP_EOL;
        }

        $file_content .= ' *' . PHP_EOL;
        $file_content .= ' * This file is automatically created.' . PHP_EOL;
        $file_content .= ' *' . PHP_EOL;
        $file_content .= ' * @site ' . $this->site_data->siteurl . PHP_EOL;
        $file_content .= ' * @time ' . current_time('D, d M Y H:i:s', true) . ' GMT' . PHP_EOL;
        $file_content .= ' */' . PHP_EOL;
        $file_content .= PHP_EOL;
        $file_content .= 'return ' . var_export($data, true) . ';';

        return $file_content;
    }

    /**
     * Writes the literal value to a file.
     *
     * @param $file_name
     * @param $content
     * @param string $file_extension
     *
     * @return string Written file path.
     */
    public static function save_to_file($file_name, $content, string $file_extension = 'txt'): string {
        if (empty($file_name) || empty($content)) {
            return '';
        }

        $settings_path = (new self)->settings_path;

        file_put_contents($settings_path . $file_name . '.' . $file_extension, $content, LOCK_EX);

        return $settings_path . $file_name . '.' . $file_extension;
    }

    /**
     * Read the content of a file.
     */
    public static function read_file($filename) {
        $path = (new self)->settings_path . $filename;

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return '';
    }

    public static function delete_file($filename): string {
        $filename = (new self)->settings_path . $filename;

        if (file_exists($filename)) {
            return unlink((new self)->settings_path . $filename);
        }

        return false;
    }

}
