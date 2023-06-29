<?php
/**
 *
 */

namespace PomatioFramework;

class Pomatio_Framework_Settings {

    public static function get_current_tab($settings_array) {
        /**
         * Skip first array key (fist key should be 'config').
         */
        if (isset($_GET['section'])) {
            return $_GET['section'];
        }

        $first_key = array_key_first($settings_array);
        return $first_key === 'config' ? array_key_first(array_slice($settings_array, 1)) : array_key_first($settings_array);
    }

    public static function get_current_subsection($settings_array) {
        if (isset($_GET['tab'])) {
            return $_GET['tab'];
        }

        $tabs = $settings_array[self::get_current_tab($settings_array)]['tab'];
        if (!empty($tabs)) {
            return array_key_first($tabs);
        }

        return null;
    }

    public static function read_fields($settings_dir, $setting_name) {
        $settings_dir = rtrim($settings_dir, '/');

        if (file_exists("$settings_dir/$setting_name/fields.php")) {
            return include "$settings_dir/$setting_name/fields.php";
        }

        return [];
    }

    public static function get_setting_value($page_slug, $setting_name, $field_name, $type = '') {
        $values = Pomatio_Framework_Disk::read_file("$setting_name.php", $page_slug, 'array');
        $value = is_array($values) && isset($values[$field_name]) ? $values[$field_name] : '';

        if (!empty($type)) {
            $sanitize_function_name = "sanitize_pom_form_$type";
            $value = $sanitize_function_name($value);
        }

        return $value;
    }

    public static function is_setting_enabled($setting_name, $page_slug): bool {
        $enabled_settings = Pomatio_Framework_Disk::read_file('enabled_settings.php', $page_slug, 'array');

        return isset($enabled_settings[$setting_name]) && $enabled_settings[$setting_name] === '1';
    }

    public static function render($page_slug, $settings_file_path): void {
        Pomatio_Framework_Save::save_settings($page_slug, $settings_file_path);

        ?>

        <div id="<?= $page_slug ?>-settings-page" class="wrap">
            <?php

            (new self)->render_tabs($page_slug, $settings_file_path);
            (new self)->render_content($page_slug, $settings_file_path);

            ?>
        </div>

        <?php
    }

    private function render_tabs($page_slug, $settings_array = []): void {
        if (empty($page_slug) || empty($settings_array)) {
            return;
        }

        $current_tab = self::get_current_tab($settings_array);
        $current_subsection = self::get_current_subsection($settings_array);

        ?>

        <nav class="nav-tab-wrapper">
            <?php

            foreach ($settings_array as $tab_key => $tab_data) {
                if ($tab_key === 'config') {
                    continue;
                }

                $active = $tab_key === $current_tab ? ' nav-tab-active' : '';

                ?>

                <a class="nav-tab<?= $active ?>" href="<?= admin_url("options-general.php?page=$page_slug&section=$tab_key") ?>">
                    <?= $tab_data['title'] ?>
                </a>

                <?php
            }

            ?>
        </nav>

        <?php

        $tabs = $settings_array[$current_tab]['tab'];
        if (!empty($tabs)) {
            ?>

            <ul class="subsubsub">
                <?php

                foreach ($tabs as $subsection_key => $subsection_data) {
                    $tab_url = get_admin_url() . "options-general.php?page=$page_slug&section=$current_tab&tab=$subsection_key";
                    $current_class = $current_subsection === $subsection_key ? ' class="current"' : '';
                    $next = next($tabs) ? ' | ' : '';

                    ?>

                    <li>
                        <a href="<?= $tab_url ?>"<?= $current_class ?>>
                            <?= $subsection_data['title'] ?>
                        </a>
                        <?= $next ?>
                    </li>

                    <?php
                }

                ?>
            </ul>

            <?php
        }
        ?>

        <br class="clear">

        <?php
    }

    private function render_content($page_slug, $settings_array = []): void {
        $current_tab = self::get_current_tab($settings_array);
        $current_subsection = self::get_current_subsection($settings_array);

        $action_url = admin_url("options-general.php?page=$page_slug&section=$current_tab&tab=$current_subsection");

        ?>

        <h1><?= $settings_array[$current_tab]['tab'][$current_subsection]['title'] ?? '' ?></h1>

        <?php

        $description =  $settings_array[$current_tab]['tab'][$current_subsection]['description'] ?? '';

        if (!empty($description)) {
            echo "<p>$description</p>";
        }

        do_action('pomatio_framework_after_description', $current_tab, $current_subsection);

        ?>

        <form method="POST" action="<?= $action_url ?>">
            <?php

            wp_nonce_field('pom_framework_save_settings', 'pom_framework_security_check');

            $settings = Pomatio_Framework_Helper::get_settings($settings_array, $current_tab, $current_subsection);
            $enabled_settings = Pomatio_Framework_Disk::read_file('enabled_settings.php', $page_slug, 'array');

            foreach ($settings as $setting_key => $setting) {
                ?>

                <h2 class="title"><?= $setting['title'] ?? '' ?></h2>

                <?php

                if (!empty($setting['description'])) {
                    echo "<p>{$setting['description']}</p>";
                }

                ?>

                <table class="form-table">
                    <tbody>

                    <?php

                    if (isset($setting['requires_initialization']) && $setting['requires_initialization'] === true) {
                        ?>

                        <tr>
                            <th scope="row">
                                <label for="<?= "$setting_key-enabled" ?>">
                                    <?php _e('Enable', 'pomatio-framework') ?>
                                </label>
                            </th>
                            <td>
                                <input type="hidden" name="<?= "{$setting_key}_enabled" ?>" value="no">
                                <input name="<?= "{$setting_key}_enabled" ?>" id="<?= "$setting_key-enabled" ?>" type="checkbox" value="yes" <?= isset($enabled_settings[$setting_key]) && $enabled_settings[$setting_key] === '1' ? 'checked' : '' ?>>
                                <label for="<?= "$setting_key-enabled" ?>"><?php _e('Check to enable this setting.', 'pomatio-framework') ?></label>

                                <?php

                                if (!empty($setting['description'])) {
                                    ?>

                                    <p class="description"><?= $setting['description'] ?></p>

                                    <?php
                                }

                                ?>

                            </td>
                        </tr>

                        <?php
                    }

                    if (
                        (isset($setting['requires_initialization']) && $setting['requires_initialization'] !== true) ||
                        (isset($enabled_settings[$setting_key]) && $enabled_settings[$setting_key] === '1')
                    ) {
                        // Check $settings_array[$_GET['section']]['settings_dir'] for plugins.
                        $settings_dir = isset($_GET['section'], $settings_array[$_GET['section']]['settings_dir']) && is_dir($settings_array[$_GET['section']]['settings_dir']) ? $settings_array[$_GET['section']]['settings_dir'] : $settings_array['config']['settings_dir'];
                        $fields = self::read_fields($settings_dir, $setting_key);
                        foreach ($fields as $field) {
                            if ($field['type'] === 'Separator') {
                                ?>

                                    </tbody>
                                </table>

                                <?php

                                echo (new Pomatio_Framework())::add_field($field);

                                ?>

                                <table class="form-table">
                                    <tbody>
                                <?php

                                continue;
                            }

                            ?>

                            <tr>
                                <th scope="row">
                                    <label for="<?= $field['name'] ?? '' ?>">
                                        <?= $field['label'] ?? '' ?>
                                    </label>
                                </th>
                                <td>
                                    <?php

                                    $description = $field['description'] ?? '';
                                    unset($field['label'], $field['description']);

                                    $value = self::get_setting_value($page_slug, $setting_key, $field['name']);

                                    $field['name'] = $setting_key . '_' . $field['name'];

                                    if ($field['type'] === 'checkbox' && isset($field['value']) && $field['value'] === true) {
                                        $field['value'] = 'yes';
                                    }
                                    elseif ($field['type'] === 'code_html' || $field['type'] === 'code_css' || $field['type'] === 'code_js') {
                                        $value = Pomatio_Framework_Disk::read_file($field['name'] . '.' . str_replace('code_', '', $field['type']), $page_slug);
                                        $sanitize_function_name = "sanitize_pom_form_{$field['type']}";
                                        $field['value'] = function_exists($sanitize_function_name) ? $sanitize_function_name($value) : $value;
                                    }
                                    else {
                                        $sanitize_function_name = "sanitize_pom_form_{$field['type']}";
                                        $field['value'] = function_exists($sanitize_function_name) ? $sanitize_function_name($value) : $value;
                                    }

                                    echo (new Pomatio_Framework())::add_field($field);

                                    ?>
                                    <p class="description" id="<?= $field['name'] ?>"><?= $description ?></p>
                                </td>
                            </tr>

                            <?php
                        }
                    }

                    ?>

                    </tbody>
                </table>

                <hr>

                <?php
            }

            ?>

            <input type="hidden" name="save_pom_framework_fields" value="1">

            <?php submit_button(__('Save', 'pomatio-framework')) ?>
        </form>

        <?php
    }

}
