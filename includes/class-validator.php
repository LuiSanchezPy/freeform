<?php
if (!defined('ABSPATH')) exit;

class FormFree_Validator {

    public function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function validate_phone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }

    public function validate_required($value) {
        return !empty(trim($value));
    }

    public function validate_number($value) {
        return is_numeric($value);
    }

    public function validate_date($date) {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public function sanitize_text($text) {
        return sanitize_text_field($text);
    }

    public function sanitize_textarea($text) {
        return sanitize_textarea_field($text);
    }

    public function sanitize_email($email) {
        return sanitize_email($email);
    }
}
