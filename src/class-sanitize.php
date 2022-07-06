<?php
/**
 * Functions to ensure that values are safe to tamper with.
 */

use POM\Form\POM_Form_Helper;

function sanitize_pom_form_button($value) {
    return $value;
}

function sanitize_pom_form_checkbox($value) {
    if (is_array($value)) {
        return !empty($value) ? $value : [];
    }
    return $value === 'yes' ? 'yes' : 'no';
}

function sanitize_pom_form_code_css($value) {
    return $value;
}

function sanitize_pom_form_code_html($value) {
    $allowed_tags = POM_Form_Helper::get_allowed_html();
    return wp_kses($value, $allowed_tags, []);
}

function sanitize_pom_form_code_js($value) {
    $filtered_js = esc_js($value);
    return stripslashes($filtered_js);
}

function sanitize_pom_form_color($value) {
    return sanitize_hex_color(sanitize_text_field($value));
}

function sanitize_pom_form_color_palette($value) {
    return sanitize_hex_color(sanitize_text_field($value));
}

function sanitize_pom_form_date($value) {
    return $value;
}

function sanitize_pom_form_datetime($value) {
    return $value;
}

function sanitize_pom_form_email($value) {
    return sanitize_email($value);
}

function sanitize_pom_form_file($value) {
    return $value;
}

function sanitize_pom_form_gallery($value) {
    return $value;
}

function sanitize_pom_form_hidden($value) {
    return sanitize_text_field($value);
}

function sanitize_pom_form_icon_picker($value) {
    return $value;
}

function sanitize_pom_form_image_picker($value) {
    return $value;
}

function sanitize_pom_form_number($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
}

function sanitize_pom_form_password($value) {
    return $value;
}

function sanitize_pom_form_quantity($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
}

function sanitize_pom_form_radio($value) {
    return sanitize_text_field($value);
}

function sanitize_pom_form_range($value) {
    return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
}

function sanitize_pom_form_repeater($value) {
    return $value;
}

function sanitize_pom_form_select($value) {
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

function sanitize_pom_form_text($value) {
    return sanitize_text_field($value);
}

function sanitize_pom_form_textarea($value) {
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
    $allowed_tags = POM_Form_Helper::get_allowed_html();
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
