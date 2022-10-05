<?php
/**
 * Functions to ensure that values are safe to tamper with.
 */

use PomatioFramework\Pomatio_Framework_Disk;
use PomatioFramework\Pomatio_Framework_Helper;

function sanitize_pom_form_button($value) {
    return $value;
}

/**
 * Returns 'yes' or 'no' if it is a single checkbox.
 * If it is an array of checkboxes, it returns a value in array format.
 *
 * @param $value
 * @return array|mixed|string
 */
function sanitize_pom_form_checkbox($value) {
    if (is_array($value)) {
        return !empty($value) ? $value : [];
    }
    return $value === 'yes' ? 'yes' : 'no';
}

function sanitize_pom_form_code_css($value, $compression_level = 'default'): string {
    $csstidy = new csstidy();

    $csstidy->set_cfg('optimise_shorthands', 2);
    $csstidy->set_cfg('template', $compression_level); // compression level

    $csstidy->parse($value);
    return $csstidy->print->plain();
}

/**
 * Filters text content and strips out disallowed HTML.
 *
 * @param $value
 * @return string
 */
function sanitize_pom_form_code_html($value): string {
    $allowed_tags = Pomatio_Framework_Helper::get_allowed_html();
    return wp_kses($value, $allowed_tags, []);
}

function sanitize_pom_form_code_js($value): string {
    $filtered_js = esc_js($value);
    return stripslashes($filtered_js);
}

function sanitize_pom_form_color($value) {
    return sanitize_hex_color(sanitize_text_field($value));
}

function sanitize_pom_form_color_palette($value) {
    return sanitize_hex_color(sanitize_text_field($value));
}

function sanitize_pom_form_date($value, $format = 'Y-m-d') {
    $timestamp = strtotime(sanitize_text_field($value));
    return date($format, $timestamp);
}

function sanitize_pom_form_datetime($value, $format = 'Y-m-d H:i') {
    $timestamp = strtotime(sanitize_text_field($value));
    return date($format, $timestamp);
}

function sanitize_pom_form_email($value): string {
    return sanitize_email($value);
}

function sanitize_pom_form_file($value) {
    $max_file_size = apply_filters('pom_form_max_file_size', 1073741824); // In bytes. Default max 1GB

    $allowed_mime_types = [
        'text/plain',
        'application/msword', // .doc
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif'
    ];
    $allowed_mime_types = apply_filters('pom_form_allowed_mime_types', $allowed_mime_types);

    // TODO: Finish sanitizing.
    return $value;
}

/**
 * Allow only numbers (from 0 to 9) and comma (,).
 *
 * @param $value
 * @return false|mixed
 */
function sanitize_pom_form_gallery($value) {
    $validate = preg_match("/^[0-9,]+$/", $value);
    return $validate ? $value : false;
}

function sanitize_pom_form_hidden($value): string {
    return sanitize_text_field($value);
}

/**
 * Validate that it is a real URL.
 *
 * @param $value
 * @return string
 */
function sanitize_pom_form_icon_picker($value): string {
    return sanitize_url($value);
}

/**
 * Validate that it is a real URL.
 *
 * @param $value
 * @return string
 */
function sanitize_pom_form_image_picker($value): string {
    return sanitize_url($value);
}

function sanitize_pom_form_number($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
}

function sanitize_pom_form_password($value) {
    return sanitize_text_field($value);
}

function sanitize_pom_form_quantity($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
}

function sanitize_pom_form_radio($value): string {
    return sanitize_text_field($value);
}

function sanitize_pom_form_range($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
}

function sanitize_pom_form_repeater($value, $array_settings = [], $settings_dir = 'pom-form') {
    if (is_string($value)) {
        $value = json_decode(stripslashes($value), true);
    }

    if (!empty($value)) {
        $sanitized_array = [];

        $limit = isset($array_settings['limit']) ? (int)$array_settings['limit'] : '';

        $i = 0;
        foreach ($value as $type => $items) {
            foreach ($items as $index => $arr_data) {
                if (!empty($limit) && $limit === $i++) {
                    break;
                }

                $repeater_identifier = '';
                foreach ($arr_data as $arr_key => $arr_value) {
                    if ($arr_key === 'repeater_identifier') {
                        $sanitized_array[$type][$index][$arr_key] = $repeater_identifier = sanitize_pom_form_text($arr_value);
                    }
                    elseif ($arr_key === 'default_values') {
                        $sanitized_array[$type][$index][$arr_key] = json_decode(stripslashes($arr_value), true);
                    }
                    else {
                        if (empty($arr_value['type'])) {
                            continue;
                        }

                        $sanitize_function_name = "sanitize_pom_form_{$arr_value['type']}";

                        if (isset($array_settings['name']) && ($arr_value['type'] === 'code_html' || $arr_value['type'] === 'code_css' || $arr_value['type'] === 'code_js')) {
                            $file_name = "{$array_settings['name']}_{$repeater_identifier}_{$arr_key}";
                            $sanitized_array[$type][$index][$arr_key]['value'] = Pomatio_Framework_Disk::save_to_file($file_name, $arr_value['value'], str_replace('code_', '', $arr_value['type']), $settings_dir);
                        }
                        else {
                            $sanitized_array[$type][$index][$arr_key]['value'] = $sanitize_function_name($arr_value['value']);
                        }

                        $sanitized_array[$type][$index][$arr_key]['type'] = $arr_value['type'];
                    }
                }
            }
        }

        return $sanitized_array;
    }

    return $value;
}

function sanitize_pom_form_select($value): string {
    return sanitize_text_field($value);
}

/**
 * Returns the entered value if it is a valid phone.
 * If not, it returns false.
 *
 * @param $value
 * @return false|mixed
 */
function sanitize_pom_form_tel($value) {
    $validate = preg_match("/^\\+?[1-9][0-9]{7,14}$/", $value);
    return $validate ? $value : false;
}

function sanitize_pom_form_text($value): string {
    return sanitize_text_field($value);
}

function sanitize_pom_form_textarea($value): string {
    return sanitize_textarea_field($value);
}

/**
 * Returns the time in HH:MM format if it passes validation.
 * If not, it returns false.
 *
 * @param $value
 * @return false|mixed
 */
function sanitize_pom_form_time($value) {
    $validate = preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $value);
    return $validate ? $value : false;
}

function sanitize_pom_form_tinymce($value): string {
    $allowed_tags = Pomatio_Framework_Helper::get_allowed_html();
    return wp_kses($value, $allowed_tags, []);
}

function sanitize_pom_form_toggle($value): string {
    return $value === 'yes' || $value === 'true' || $value === true ? 'yes' : 'no';
}

/**
 * Checks and cleans a URL.
 *
 * @param $value
 * @return string
 */
function sanitize_pom_form_url($value): string {
    return sanitize_url($value);
}
