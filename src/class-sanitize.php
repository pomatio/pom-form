<?php
/**
 * Functions to ensure that values are safe to tamper with.
 */

use PomatioFramework\Pomatio_Framework_Disk;
use PomatioFramework\Pomatio_Framework_Helper;

if (!function_exists('sanitize_pom_form_background_image')) {
    function sanitize_pom_form_background_image($value) {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        return json_decode(stripslashes($value), true);
    }
}

if (!function_exists('sanitize_pom_form_button')) {
    function sanitize_pom_form_button($value) {
        return $value;
    }
}

/**
 * Returns 'yes' or 'no' if it is a single checkbox.
 * If it is an array of checkboxes, it returns a value in array format.
 *
 * @param $value
 *
 * @return array|mixed|string
 */
if (!function_exists('sanitize_pom_form_checkbox')) {
    function sanitize_pom_form_checkbox($value) {
        if (is_array($value)) {
            return !empty($value) ? $value : [];
        }

        return $value === 'yes' ? 'yes' : 'no';
    }
}

if (!function_exists('sanitize_pom_form_toggle')) {
    function sanitize_pom_form_toggle($value): string {
        return $value === 'yes' ? 'yes' : 'no';
    }
}

if (!function_exists('sanitize_pom_form_code_css')) {
    // TODO: Fix this
    function sanitize_pom_form_code_css($value, $compression_level = 'default'): string {
/*        $csstidy = new csstidy();
        $csstidy->set_cfg('optimise_shorthands', 2);
        $csstidy->set_cfg('template', $compression_level); // compression level
        $csstidy->parse($value);*/

        return $value;
    }
}

/**
 * Filters text content and strips out disallowed HTML.
 *
 * @param $value
 *
 * @return string
 */
if (!function_exists('sanitize_pom_form_code_html')) {
    function sanitize_pom_form_code_html($value): string {
        $allowed_tags = Pomatio_Framework_Helper::get_allowed_html();

        return wp_kses(stripslashes($value), $allowed_tags);
    }
}

if (!function_exists('sanitize_pom_form_code_js')) {
    function sanitize_pom_form_code_js($value): string {
        $filtered_js = esc_js($value);

        return stripslashes($filtered_js);
    }
}

if (!function_exists('sanitize_pom_form_code_json')) {
    function sanitize_pom_form_code_json($value): string {
        $filtered_js = esc_js($value);

        return stripslashes($filtered_js);
    }
}

if (!function_exists('sanitize_pom_form_color')) {
    function sanitize_pom_form_color($value): string {
        return sanitize_hex_color(sanitize_text_field($value));
    }
}

if (!function_exists('sanitize_pom_form_color_palette')) {
    function sanitize_pom_form_color_palette($value): string {
        return sanitize_text_field($value);
    }
}

if (!function_exists('sanitize_pom_form_date')) {
    function sanitize_pom_form_date($value, $format = 'Y-m-d') {
        $timestamp = strtotime(sanitize_text_field($value));

        return date($format, $timestamp);
    }
}

if (!function_exists('sanitize_pom_form_datetime')) {
    function sanitize_pom_form_datetime($value, $format = 'Y-m-d H:i') {
        $timestamp = strtotime(sanitize_text_field($value));

        return date($format, $timestamp);
    }
}

if (!function_exists('sanitize_pom_form_email')) {
    function sanitize_pom_form_email($value): string {
        return sanitize_email($value);
    }
}

if (!function_exists('sanitize_pom_form_file')) {
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
}

/**
 * Allow only numbers (from 0 to 9) and comma (,).
 *
 * @param $value
 *
 * @return false|mixed
 */
if (!function_exists('sanitize_pom_form_gallery')) {
    function sanitize_pom_form_gallery($value) {
        $validate = preg_match("/^[0-9,]+$/", $value);

        return $validate ? $value : false;
    }
}

if (!function_exists('sanitize_pom_form_hidden')) {
    function sanitize_pom_form_hidden($value): string {
        return sanitize_text_field($value);
    }
}

/**
 * Validate that it is a real URL.
 *
 * @param $value
 *
 * @return string
 */
if (!function_exists('sanitize_pom_form_icon_picker')) {
    function sanitize_pom_form_icon_picker($value): string {
        return sanitize_url($value);
    }
}

/**
 * Validate that it is a real URL.
 *
 * @param $value
 *
 * @return string
 */
if (!function_exists('sanitize_pom_form_image_picker')) {
    function sanitize_pom_form_image_picker($value): string {
        return sanitize_url($value);
    }
}

if (!function_exists('sanitize_pom_form_number')) {
    function sanitize_pom_form_number($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
    }
}

if (!function_exists('sanitize_pom_form_password')) {
    function sanitize_pom_form_password($value): string {
        return sanitize_text_field($value);
    }
}

if (!function_exists('sanitize_pom_form_quantity')) {
    function sanitize_pom_form_quantity($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
    }
}

if (!function_exists('sanitize_pom_form_radio')) {
    function sanitize_pom_form_radio($value): string {
        return sanitize_text_field($value);
    }
}

if (!function_exists('sanitize_pom_form_radio_icons')) {
    function sanitize_pom_form_radio_icons($value): string {
        return sanitize_text_field($value);
    }
}

if (!function_exists('sanitize_pom_form_range')) {
    function sanitize_pom_form_range($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
}

if (!function_exists('sanitize_pom_form_trbl')) {
    function sanitize_pom_form_trbl($value): array {
        if (is_string($value)) {
            $value = json_decode(stripslashes($value), true);
        }

        if (!is_array($value)) {
            return [];
        }

        $sides = ['top', 'right', 'bottom', 'left'];
        $sanitized = [
            'sync' => (!empty($value['sync']) && $value['sync'] === 'yes') ? 'yes' : 'no'
        ];

        foreach ($sides as $side) {
            $side_value = '';
            $side_unit = '';

            if (!empty($value[$side]) && is_array($value[$side])) {
                $side_value = isset($value[$side]['value']) ? filter_var($value[$side]['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
                $side_unit = isset($value[$side]['unit']) ? sanitize_text_field($value[$side]['unit']) : '';
            }

            $sanitized[$side] = [
                'value' => $side_value,
                'unit'  => $side_unit,
            ];
        }

        return $sanitized;
    }
}

if (!function_exists('sanitize_pom_form_font')) {
    function sanitize_pom_form_font($value) {
        return sanitize_pom_form_repeater($value);
    }
}

if (!function_exists('sanitize_pom_form_font_picker')) {
    function sanitize_pom_form_font_picker($value): array {
        if (is_string($value)) {
            $value = str_replace('&quot;', '"', $value);
            $value = json_decode($value, true);
        }

        if (!is_array($value)) {
            return [];
        }

        $font_extensions = array_keys(Pomatio_Framework_Helper::get_allowed_font_types());
        $sanitized = [];
        foreach ($value as $font_extension => $font_url) {
            if (!in_array($font_extension, $font_extensions, true)) {
                continue;
            }

            $sanitized[$font_extension] = sanitize_url($font_url);
        }

        return $sanitized;
    }
}

if (!function_exists('sanitize_pom_form_repeater')) {
    function sanitize_pom_form_repeater($value, $array_settings = [], $settings_dir = 'pomatio-framework'): array {
        if (is_string($value)) {
            $value = json_decode(stripslashes($value), true);
        }

        if (!empty($value)) {
            $sanitized_array = [];

            $limit = !empty($array_settings['limit']) ? (int)$array_settings['limit'] : '';

            $i = 0;
            foreach ($value as $type => $items) {
                foreach ($items as $index => $arr_data) {
                    if (!empty($limit) && $limit === $i ++) {
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
                            $field_name = str_replace('[]', '', $arr_key);

                            if (isset($array_settings['name']) && ($arr_value['type'] === 'code_html' || $arr_value['type'] === 'code_css' || $arr_value['type'] === 'code_js' || $arr_value['type'] === 'code_json')) {
                                $file_name = "{$array_settings['name']}_{$repeater_identifier}_$field_name";
                                $sanitized_array[$type][$index][$field_name]['value'] = Pomatio_Framework_Disk::save_to_file($file_name, $arr_value['value'], str_replace('code_', '', $arr_value['type']), $settings_dir);
                            }
                            else {
                                $sanitized_array[$type][$index][$field_name]['value'] = $sanitize_function_name($arr_value['value']);
                            }

                            $sanitized_array[$type][$index][$field_name]['type'] = $arr_value['type'];
                        }
                    }
                }
            }

            return $sanitized_array;
        }

        return [];
    }
}

if (!function_exists('sanitize_pom_form_select')) {
    function sanitize_pom_form_select($value): string {
        if (is_array($value)) {
            return sanitize_text_field(implode(',', $value));
        }

        return sanitize_text_field($value);
    }
}

if (!function_exists('sanitize_pom_form_signature')) {
    function sanitize_pom_form_signature($value): string {
        return $value;
    }
}

/**
 * Returns the entered value if it is a valid phone.
 * If not, it returns false.
 *
 * @param $value
 *
 * @return false|mixed
 */
if (!function_exists('sanitize_pom_form_tel')) {
    function sanitize_pom_form_tel($value) {
        $validate = preg_match("/^\\+?[1-9][0-9]{7,14}$/", $value);

        return $validate ? $value : false;
    }
}

if (!function_exists('sanitize_pom_form_text')) {
    function sanitize_pom_form_text($value): string {
        return sanitize_text_field($value);
    }
}

if (!function_exists('sanitize_pom_form_textarea')) {
    function sanitize_pom_form_textarea($value): string {
        return sanitize_textarea_field($value);
    }
}

/**
 * Returns the time in HH:MM format if it passes validation.
 * If not, it returns false.
 *
 * @param $value
 *
 * @return false|mixed
 */
if (!function_exists('sanitize_pom_form_time')) {
    function sanitize_pom_form_time($value) {
        $validate = preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $value);

        return $validate ? $value : false;
    }
}

if (!function_exists('sanitize_pom_form_tinymce')) {
    function sanitize_pom_form_tinymce($value): string {
        $allowed_tags = Pomatio_Framework_Helper::get_allowed_html();

        return wp_kses($value, $allowed_tags);
    }
}

if (!function_exists('sanitize_pom_form_url')) {
    function sanitize_pom_form_url($value): string {
        return $value;
    }
}
