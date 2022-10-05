<?php
/**
 * Class used for handling disk-related operations.
 */

namespace PomatioFramework;

class Pomatio_Framework_Disk {

    /**
     * Current site data.
     */
    private $site_data;

    public function __construct() {
        $this->site_data = get_blog_details();
    }

    /**
     * Generate .htaccess file on dir creation for security purposes.
     *
     * @return void
     */
    private function create_htaccess_file(): void {
        $htaccess = WP_CONTENT_DIR . "/settings/.htaccess";

        if (!is_file($htaccess)) {
$htaccess_content = '<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>
<FilesMatch "\.(htaccess|htpasswd|ini|phps?|fla|psd|log|sh|zip|exe|pl|jsp|asp|htm|cgi|py|php|shtml)$">
    ForceType text/plain
</FilesMatch>
';
            file_put_contents($htaccess, trim($htaccess_content));
        }
    }

    /**
     * Establish the path in which the actions related to files are executed.
     *
     * @param string $settings_dir
     *
     * @return string
     */
    public function get_settings_path(string $settings_dir = 'pomatio-framework'): string {
        $multisite_path = is_multisite() ? "sites/{$this->site_data->blog_id}/" : '';

        return WP_CONTENT_DIR . "/settings/$settings_dir/$multisite_path";
    }

    /**
     * Creates the directory in which the configuration files are saved.
     * If a multisite, it is subdivided into /sites/blog_id/.
     *
     * @param string $settings_dir
     *
     * @return void
     */
    public static function create_settings_dir(string $settings_dir = 'pomatio-framework'): void {
        $settings_path = (new self)->get_settings_path($settings_dir);
        if (!is_dir($settings_path)) {
            $created = wp_mkdir_p($settings_path);

            if (!$created) {
                Pomatio_Framework_Helper::write_log('Error creating tweaks settings dir.');
            }
            else {
                (new self)->create_enabled_settings_file();
                (new self)->create_htaccess_file();
                Pomatio_Framework_Helper::write_log('Created tweaks settings dir.');
            }
        }
    }

    private function create_enabled_settings_file(): void {
        $this->generate_file_content([], 'File responsible for saving active settings info.');
    }

    /**
     * Generates the file content by converting the data to an array.
     *
     * @param $data
     * @param string $description
     *
     * @return string
     */
    public function generate_file_content($data, string $description = ''): string {
        if (empty($data)) {
            return '';
        }

        $file_content = '<?php' . PHP_EOL;
        $file_content .= '/**' . PHP_EOL;
        $file_content .= !empty($description) ? ' * ' . $description . PHP_EOL : '';
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
     * @param string $settings_dir
     *
     * @return string Written file path.
     */
    public static function save_to_file($file_name, $content, string $file_extension = 'txt', string $settings_dir = 'pomatio-framework'): string {
        if (empty($file_name) || empty($content)) {
            return '';
        }

        $settings_path = (new self)->get_settings_path($settings_dir);

        file_put_contents($settings_path . $file_name . '.' . $file_extension, $content, LOCK_EX);

        return $settings_path . $file_name . '.' . $file_extension;
    }

    /**
     * Read the content of a file.
     */
    public static function read_file($filename, $settings_dir = 'pomatio-framework', $return = 'default') {
        $settings_path = (new self)->get_settings_path($settings_dir);
        $path = $settings_path . $filename;

        if (file_exists($path)) {
            return $return === 'array' ? include $path : file_get_contents($path);
        }

        return '';
    }

    /**
     * Deletes selected file.
     *
     * @param $filename
     * @param string $settings_dir
     *
     * @return bool
     */
    public static function delete_file($filename, string $settings_dir = 'pomatio-framework'): bool {
        $settings_path = (new self)->get_settings_path($settings_dir);
        $filename = $settings_path . $filename;

        return file_exists($filename) && unlink($filename);
    }

}
