<?php
/**
 * Class used for handling disk-related operations.
 */

namespace PomatioFramework;

class Pomatio_Framework_Disk {

    public function __construct() {
        /**
         * Set allowed font mime types and save the fonts in a custom directory.
         */
        add_filter('upload_dir', [$this, 'set_fonts_upload_dir']);
        add_filter('upload_mimes', [$this, 'add_allowed_font_mimes_to_upload_types']);
    }

    public function set_fonts_upload_dir($path) {
        if (!isset($_POST['name']) || is_array($_POST['name'])) {
            return $path;
        }

        $extension = substr(strrchr($_POST['name'], '.'), 1);

        $font_extensions = array_keys(Pomatio_Framework_Helper::get_allowed_font_types());

        if (!empty($path['error']) || !in_array($extension, $font_extensions, true)) {
            return $path;
        }

        $custom_dir = '/fonts';
        $path['path'] = str_replace($path['subdir'], '', $path['path']); //remove default subdir (year/month)
        $path['url'] = str_replace($path['subdir'], '', $path['url']);
        $path['subdir'] = $custom_dir;
        $path['path'] .= $custom_dir;
        $path['url'] .= $custom_dir;

        return $path;
    }

    public function add_allowed_font_mimes_to_upload_types($mimes) {
        $allowed_fonts = Pomatio_Framework_Helper::get_allowed_font_types();

        foreach ($allowed_fonts as $font => $mime) {
            $mimes[$font] = $mime;
        }

        return $mimes;
    }

    /**
     * Generate .htaccess file on dir creation for security purposes.
     *
     * @return void
     */
    public function create_htaccess_file(): void {
        $htaccess = WP_CONTENT_DIR . "/settings/.htaccess";

        if (!is_file($htaccess)) {
            $htaccess_content = <<<'HTACCESS'
# Default blocking
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule mod_access_compat.c>
    Order deny,allow
    Deny from all
</IfModule>

# Allow ONLY CSS/JS (and sourcemaps) y and ONLY with GET/HEAD/OPTIONS
<FilesMatch "\.(?:css|js|map)$">
    <IfModule mod_authz_core.c>
        Require all granted
        <LimitExcept GET HEAD OPTIONS>
            Require all denied
        </LimitExcept>
    </IfModule>
    <IfModule mod_access_compat.c>
        Order allow,deny
        Allow from all
        <LimitExcept GET HEAD OPTIONS>
            Deny from all
        </LimitExcept>
    </IfModule>
</FilesMatch>

# Never execute server-side on this directory
Options -Indexes -ExecCGI

# Deactivate PHP / CGI / scripts (force plaintext if someone hits)
<FilesMatch "\.(?:php[0-9]?|phtml|phps|shtml|cgi|pl|py|jsp|asp)$">
    ForceType text/plain
    RemoveHandler .php .phtml .phps .shtml .cgi .pl .py .jsp .asp
    RemoveType .php .phtml .phps .shtml .cgi .pl .py .jsp .asp
</FilesMatch>

# Block dotfiles (.env, .git, etc.)
<FilesMatch "^\.(.*)$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule mod_access_compat.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>

# Harden headers for static files
<IfModule mod_headers.c>
    <FilesMatch "\.(?:css|js|map)$">
        Header set X-Content-Type-Options "nosniff"
        Header set Referrer-Policy "strict-origin-when-cross-origin"
        Header set Cache-Control "public, max-age=2592000, immutable"
    </FilesMatch>
</IfModule>

# Cache expires
<IfModule mod_expires.c>
    ExpiresActive On
    <FilesMatch "\.(?:css|js|map)$">
        ExpiresDefault "access plus 30 days"
    </FilesMatch>
</IfModule>
HTACCESS;
            file_put_contents($htaccess, trim($htaccess_content));
        }
    }

    public function save_signature_image($data) {
        $multisite_path = is_multisite() && !is_main_site() ? 'sites/' . get_current_blog_id() . '/' : '';
        $uploads_array = wp_get_upload_dir();
        $path = trailingslashit($uploads_array['basedir']) . $multisite_path . 'signatures/';

        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                return false;
            }

            $htaccessContent = "deny from all";
            $htaccessPath = $path . '/.htaccess';
            file_put_contents($htaccessPath, $htaccessContent);
        }

        $filename = Pomatio_Framework_Helper::generate_random_string(20, false) . '.png';
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data));
        $saved = file_put_contents($path . $filename, $data);

        return $saved ? $path . $filename : false;
    }

    public function get_signature_image($path): string {
        if (!file_exists($path) || !is_readable($path)) {
            return '';
        }

        $image_data = file_get_contents($path);
        if ($image_data === false) {
            return '';
        }

        $image_base64 = base64_encode($image_data);

        return 'data:image/png;base64,' . $image_base64;
    }

    /**
     * Establish the path in which the actions related to files are executed.
     *
     * @param string $settings_dir
     *
     * @return string
     */
    public function get_settings_path(string $settings_dir = 'pomatio-framework'): string {
        $multisite_path = is_multisite() ? 'sites/' . get_current_blog_id() . '/' : '';

        return WP_CONTENT_DIR . "/settings/pomatio-framework/$multisite_path$settings_dir/";
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
        if (empty($data) && empty($description)) {
            return '';
        }

        $data = empty($data) ? [] : $data;

        $file_content = '<?php' . PHP_EOL;
        $file_content .= '/**' . PHP_EOL;
        $file_content .= !empty($description) ? ' * ' . $description . PHP_EOL : '';
        $file_content .= ' *' . PHP_EOL;
        $file_content .= ' * This file is automatically created.' . PHP_EOL;
        $file_content .= ' *' . PHP_EOL;
        $file_content .= ' * @site ' . get_site_url() . PHP_EOL;
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

        return $return === 'array' ? [] : '';
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
