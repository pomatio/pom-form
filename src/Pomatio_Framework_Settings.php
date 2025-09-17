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
        $storage_metadata = self::get_field_storage_metadata($page_slug, $setting_name, $field_name);

        if (!empty($storage_metadata)) {
            $default_value = $storage_metadata['default'] ?? '';

            if (($storage_metadata['storage'] ?? '') === 'theme_mod') {
                $value = get_theme_mod($field_name, $default_value);
            }
            elseif (($storage_metadata['storage'] ?? '') === 'option') {
                $value = get_option($field_name, $default_value);
            }
            else {
                $values = Pomatio_Framework_Disk::read_file("$setting_name.php", $page_slug, 'array');
                $value = is_array($values) && isset($values[$field_name]) ? $values[$field_name] : '';
            }
        }
        else {
            $values = Pomatio_Framework_Disk::read_file("$setting_name.php", $page_slug, 'array');
            $value = is_array($values) && isset($values[$field_name]) ? $values[$field_name] : '';
        }

        if (!empty($type)) {
            $sanitize_function_name = "sanitize_pom_form_$type";

            if (function_exists($sanitize_function_name)) {
                $value = $sanitize_function_name($value);
            }
        }

        return $value;
    }

    private static function get_field_storage_metadata(string $page_slug, string $setting_name, string $field_name): array {
        $map = self::get_fields_save_as_map($page_slug);

        if (
            isset($map[$setting_name]) &&
            is_array($map[$setting_name]) &&
            isset($map[$setting_name][$field_name]) &&
            is_array($map[$setting_name][$field_name])
        ) {
            return $map[$setting_name][$field_name];
        }

        return [];
    }

    private static function get_fields_save_as_map(string $page_slug): array {
        static $cache = [];

        if (!isset($cache[$page_slug])) {
            $map = Pomatio_Framework_Disk::read_file('fields_save_as.php', $page_slug, 'array');
            $cache[$page_slug] = is_array($map) ? $map : [];
        }

        return $cache[$page_slug];
    }

    public static function is_setting_enabled($setting_name, $page_slug, array $settings_array = []): bool {
        if (!empty($settings_array)) {
            $enabled_settings = self::get_effective_enabled_settings($page_slug, $settings_array);
        }
        else {
            $enabled_settings = Pomatio_Framework_Disk::read_file('enabled_settings.php', $page_slug, 'array');
        }

        return isset($enabled_settings[$setting_name]) && $enabled_settings[$setting_name] === '1';
    }

    public static function get_effective_enabled_settings(string $page_slug, array $settings_array): array {
        Pomatio_Framework_Disk::create_settings_dir($page_slug);

        $enabled_settings = (array)Pomatio_Framework_Disk::read_file('enabled_settings.php', $page_slug, 'array');
        $definitions = self::flatten_settings_definitions($settings_array);
        $needs_write = false;

        foreach ($definitions as $setting_key => $setting_definition) {
            if (isset($setting_definition['requires_initialization']) && $setting_definition['requires_initialization'] === false) {
                if (!isset($enabled_settings[$setting_key]) || $enabled_settings[$setting_key] !== '1') {
                    $enabled_settings[$setting_key] = '1';
                    $needs_write = true;
                }
            }
        }

        if ($needs_write) {
            ksort($enabled_settings);

            $disk = new Pomatio_Framework_Disk();
            $settings_path = $disk->get_settings_path($page_slug);
            $settings_content = $disk->generate_file_content($enabled_settings, 'Enabled settings array file.');

            file_put_contents($settings_path . 'enabled_settings.php', $settings_content, LOCK_EX);

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($settings_path . 'enabled_settings.php', true);
            }
        }

        return $enabled_settings;
    }

    private static function flatten_settings_definitions(array $settings_array): array {
        $settings = [];

        foreach ($settings_array as $tab_key => $tab_definition) {
            if ($tab_key === 'config' || !isset($tab_definition['tab']) || !is_array($tab_definition['tab'])) {
                continue;
            }

            foreach ($tab_definition['tab'] as $subsection_definition) {
                if (!isset($subsection_definition['settings']) || !is_array($subsection_definition['settings'])) {
                    continue;
                }

                foreach ($subsection_definition['settings'] as $setting_key => $setting_definition) {
                    $settings[$setting_key] = $setting_definition;
                }
            }
        }

        return $settings;
    }

    public static function render($page_slug, $settings_file_path): void {
        Pomatio_Framework_Save::save_settings($page_slug, $settings_file_path);

        ?>

        <div id="<?= $page_slug ?>-settings-page" class="wrap">
            <?php

            //(new self)->render_tabs($page_slug, $settings_file_path);
            (new self)->render_content($page_slug, $settings_file_path);

            ?>
        </div>

        <?php
    }

    public static function render_tabs($page_slug, $settings_array = []): void {
        global $pagenow;

        if (empty($page_slug) || empty($settings_array)) {
            return;
        }

        $current_user_role = Pomatio_Framework_Helper::get_current_user_role();
        $current_tab = self::get_current_tab($settings_array);
        $current_subsection = self::get_current_subsection($settings_array);

        ?>

        <div class="pomatio-framework-settings-nav-heading"><?php // TODO: add class .is-scrolled with js when it is scrolled. ?>

            <h1><?= $settings_array[$current_tab]['tab'][$current_subsection]['title'] ?? '' ?></h1>

        </div>

        <nav class="nav-tab-wrapper">
            <?php

            foreach ($settings_array as $tab_key => $tab_data) {
                if ($tab_key === 'config') {
                    continue;
                }

                if (isset($tab_data['allowed_roles'])) {
                    if (is_super_admin(get_current_user_id())) {
                        // Allow everything
                    }
                    elseif (!in_array($current_user_role, $tab_data['allowed_roles'], true)) {
                        continue;
                    }
                }

                $active = $tab_key === $current_tab ? ' nav-tab-active' : '';

                ?>

                <a class="nav-tab<?= $active ?>" href="<?= admin_url($pagenow ."?page=$page_slug&section=$tab_key") ?>">
                    <?= $tab_data['title'] ?>
                </a>

                <?php
            }

            ?>
        </nav>

        <?php

        if (!self::is_allowed_role($settings_array)) {
            return;
        }

        $tabs = $settings_array[$current_tab]['tab'];
        if (!empty($tabs)) {
            ?>

            <ul class="subsubsub">
                <?php

                foreach ($tabs as $subsection_key => $subsection_data) {
                    $tab_url = get_admin_url() . $pagenow ."?page=$page_slug&section=$current_tab&tab=$subsection_key";
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



        <?php
    }

    private function render_content($page_slug, $settings_array = []): void {
        $current_tab = self::get_current_tab($settings_array);
        $current_subsection = self::get_current_subsection($settings_array);

        if (!$this->is_allowed_role($settings_array)) {
            (new self)->render_error_page($settings_array);

            return;
        }

        $action_url = admin_url("options-general.php?page=$page_slug&section=$current_tab&tab=$current_subsection");







        $description = $settings_array[$current_tab]['tab'][$current_subsection]['description'] ?? '';

        if (!empty($description)) {
            echo "<p>$description</p>";
        }

        do_action('pomatio_framework_after_description', $current_tab, $current_subsection);

        ?>

        <form method="POST" class="pomatio-framework-settings-form" action="<?= $action_url ?>">
            <?php

            wp_nonce_field('pom_framework_save_settings', 'pom_framework_security_check');

            $settings = Pomatio_Framework_Helper::get_settings($settings_array, $current_tab, $current_subsection);
            $enabled_settings = self::get_effective_enabled_settings($page_slug, $settings_array);

            foreach ($settings as $setting_key => $setting) {

                $wrapper_is_div = false;
                if(!empty($setting['wrapper']) && $setting['wrapper'] === 'div') {
                    $wrapper_is_div = true;
                }

                if ($wrapper_is_div) { ?>
                    <div class="pomatio-framework-setting">
                <?php }

                if (!empty($setting['img'])) {
                    ?>

                    <img src="<?= $setting['img'] ?>"/>

                    <?php
                }
                ?>

                <h2 class="title"><?= $setting['title'] ?? '' ?></h2>

                <?php

                if (!empty($setting['description'])) {
                    echo "<p>{$setting['description']}</p>";
                }

                $requires_initialization = isset($setting['requires_initialization']) && $setting['requires_initialization'] === true;

                if ($requires_initialization) {

                    if (!$wrapper_is_div) { ?>
                        <table class="form-table">
                        <tbody>
                        <tr>
                        <th scope="row">
                    <?php } ?>


                            <label class="main-label" for="<?= "$setting_key-enabled" ?>">
                                <?php

                                if (!empty($setting['heading_checkbox'])) {
                                    echo $setting['heading_checkbox'];
                                } else {
                                    _e('Enable', 'pomatio-framework');
                                }

                                ?>
                            </label><br>
                    <?php if (!$wrapper_is_div) { ?>
                        </th>
                        <td>
                    <?php } ?>

                            <input type="hidden" name="<?= "{$setting_key}_enabled" ?>" value="no">
                            <input name="<?= "{$setting_key}_enabled" ?>" id="<?= "$setting_key-enabled" ?>" type="checkbox" value="yes" <?= isset($enabled_settings[$setting_key]) && $enabled_settings[$setting_key] === '1' ? 'checked' : '' ?>>
                            <label for="<?= "$setting_key-enabled" ?>">
                                <?php

                                if (!empty($setting['label_checkbox'])) {
                                    ?>

                                    <?= $setting['label_checkbox'] ?>

                                    <?php
                                } else {
                                    _e('Check to enable this setting.', 'pomatio-framework');
                                }



                                ?>

                            </label>





                            <?php

                            if (!empty($setting['description_checkbox'])) {
                                ?>

                                <p class="description"><?= $setting['description_checkbox'] ?></p>

                                <?php
                            }

                            ?>
                <?php if (!$wrapper_is_div) { ?>

                        </td>
                        </tr>

                    </tbody>
                    </table>
                <?php } ?>


                    <?php
                }
                else {
                    ?>
                    <input type="hidden" name="<?= "{$setting_key}_enabled" ?>" value="yes">
                    <?php

                    $has_checkbox_copy = !empty($setting['heading_checkbox']) || !empty($setting['label_checkbox']) || !empty($setting['description_checkbox']);

                    if ($has_checkbox_copy) {
                        if (!$wrapper_is_div) { ?>
                            <table class="form-table">
                            <tbody>
                            <tr>
                            <th scope="row">
                        <?php } ?>

                        <span class="main-label">
                            <?= !empty($setting['heading_checkbox']) ? $setting['heading_checkbox'] : __('Enable', 'pomatio-framework') ?>
                        </span><br>
                        <?php

                        if (!$wrapper_is_div) { ?>
                            </th>
                            <td>
                        <?php } ?>

                        <div class="pomatio-framework-setting__auto-enabled-text">
                            <?= !empty($setting['label_checkbox']) ? $setting['label_checkbox'] : __('Check to enable this setting.', 'pomatio-framework') ?>
                        </div>
                        <?php

                        if (!empty($setting['description_checkbox'])) {
                            ?>
                            <p class="description"><?= $setting['description_checkbox'] ?></p>
                            <?php
                        }

                        if (!$wrapper_is_div) { ?>
                            </td>
                            </tr>
                            </tbody>
                            </table>
                        <?php }
                    }
                }

                if ($wrapper_is_div) { ?>
                    </div>
                <?php }

                if (
                    (!$requires_initialization) ||
                    (isset($enabled_settings[$setting_key]) && $enabled_settings[$setting_key] === '1')
                ) {
                    $settings_dir = (
                        isset($_GET['section'], $_GET['tab']) &&
                        isset($settings_array[$_GET['section']]['tab']) &&
                        isset($settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir']) &&
                        is_dir($settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir'])
                    ) ? $settings_array[$_GET['section']]['tab'][$_GET['tab']]['settings_dir'] : '';

                    if (empty($settings_dir)) {
                        $settings_dir = isset($_GET['section'], $settings_array[$_GET['section']]['settings_dir']) && is_dir($settings_array[$_GET['section']]['settings_dir']) ? $settings_array[$_GET['section']]['settings_dir'] : $settings_array['config']['settings_dir'];
                    } 

                    $fields = self::read_fields($settings_dir, $setting_key);

                    if(count($fields) > 0) { ?>

                    <table class="form-table">
                    <tbody>
                    <?php

                    }



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

                                $original_field_name = $field['name'];
                                $value = self::get_setting_value($page_slug, $setting_key, $original_field_name);
                                $storage_metadata = self::get_field_storage_metadata($page_slug, $setting_key, $original_field_name);
                                $field['name'] = $setting_key . '_' . $original_field_name;

                                if ($field['type'] === 'checkbox' && isset($field['value']) && $field['value'] === true) {
                                    $field['value'] = 'yes';
                                }
                                elseif ($field['type'] === 'code_html' || $field['type'] === 'code_css' || $field['type'] === 'code_js') {
                                    $source_value = $value;

                                    if (empty($storage_metadata)) {
                                        $source_value = Pomatio_Framework_Disk::read_file($field['name'] . '.' . str_replace('code_', '', $field['type']), $page_slug);
                                    }

                                    $sanitize_function_name = "sanitize_pom_form_{$field['type']}";
                                    $field['value'] = function_exists($sanitize_function_name) ? $sanitize_function_name($source_value) : $source_value;
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

                    if(count($fields) > 0) { ?>

                    </table>
                    </tbody>
                    <?php

                    }
                }

                 ?>

                


                <?php if (!$wrapper_is_div) { ?>

                    <hr>

                <?php }

            }

            ?>

            <input type="hidden" name="save_pom_framework_fields" value="1">

            <?php submit_button(__('Save', 'pomatio-framework')) ?>
        </form>

        <?php
    }

    public static function is_allowed_role($settings_array): bool {
        $current_tab = self::get_current_tab($settings_array);
        $current_user_role = Pomatio_Framework_Helper::get_current_user_role();
        $allowed_roles = $settings_array[$current_tab]['allowed_roles'] ?? [];
        $is_super_admin = is_super_admin(get_current_user_id());

        if (!$is_super_admin && !in_array($current_user_role, $allowed_roles, true)) {
            return false;
        }

        return true;
    }

    private function render_error_page($settings_array): void {
        if (!$this->is_allowed_role($settings_array)) {
            echo '<h2>' . __('You do not have access to this page', 'pomatio-framework') . '</h2>';
        }
    }

}