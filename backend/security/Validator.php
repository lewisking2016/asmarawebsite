<?php
/**
 * Input Validator Class
 * Validates and sanitizes user input
 */

class Validator {
    private static $errors = [];

    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    /**
     * Validate phone
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($phone) >= 10 ? true : false;
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    public static function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate time format (HH:MM)
     */
    public static function validateTime($time) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time) ? true : false;
    }

    /**
     * Validate future date
     */
    public static function validateFutureDate($date) {
        if (!self::validateDate($date)) {
            return false;
        }
        return strtotime($date) > strtotime('today');
    }

    /**
     * Validate number
     */
    public static function validateNumber($value, $min = null, $max = null) {
        if (!is_numeric($value)) {
            return false;
        }

        if ($min !== null && $value < $min) {
            return false;
        }

        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate integer
     */
    public static function validateInteger($value, $min = null, $max = null) {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return false;
        }

        if ($min !== null && $value < $min) {
            return false;
        }

        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate string length
     */
    public static function validateLength($string, $min = null, $max = null) {
        $length = strlen($string);

        if ($min !== null && $length < $min) {
            return false;
        }

        if ($max !== null && $length > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate required field
     */
    public static function validateRequired($value) {
        return !empty(trim($value));
    }

    /**
     * Validate in array
     */
    public static function validateInArray($value, $array) {
        return in_array($value, $array);
    }

    /**
     * Validate username
     */
    public static function validateUsername($username) {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username) ? true : false;
    }

    /**
     * Validate password strength
     */
    public static function validatePassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) ? true : false;
    }

    /**
     * Sanitize string
     */
    public static function sanitizeString($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize integer
     */
    public static function sanitizeInteger($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float
     */
    public static function sanitizeFloat($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize URL
     */
    public static function sanitizeURL($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Escape HTML output
     */
    public static function escape($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate booking data
     */
    public static function validateBooking($data) {
        self::$errors = [];

        if (!self::validateRequired($data['guest_name'] ?? '')) {
            self::$errors[] = 'Guest name is required';
        }

        if (!self::validateEmail($data['email'] ?? '')) {
            self::$errors[] = 'Valid email is required';
        }

        if (!self::validatePhone($data['phone'] ?? '')) {
            self::$errors[] = 'Valid phone is required';
        }

        if (!self::validateFutureDate($data['booking_date'] ?? '')) {
            self::$errors[] = 'Booking date must be in the future';
        }

        if (!self::validateTime($data['booking_time'] ?? '')) {
            self::$errors[] = 'Valid booking time is required';
        }

        if (!self::validateInteger($data['guest_count'] ?? 0, 1, 100)) {
            self::$errors[] = 'Guest count must be between 1 and 100';
        }

        if (!self::validateInteger($data['branch_id'] ?? 0, 1)) {
            self::$errors[] = 'Valid branch is required';
        }

        return empty(self::$errors);
    }

    /**
     * Validate menu item
     */
    public static function validateMenuItem($data) {
        self::$errors = [];

        if (!self::validateRequired($data['name'] ?? '')) {
            self::$errors[] = 'Menu item name is required';
        }

        if (!self::validateRequired($data['category'] ?? '')) {
            self::$errors[] = 'Category is required';
        }

        if (!self::validateNumber($data['price'] ?? 0, 0, 100000)) {
            self::$errors[] = 'Price must be between 0 and 100000';
        }

        return empty(self::$errors);
    }

    /**
     * Validate contact message
     */
    public static function validateContact($data) {
        self::$errors = [];

        if (!self::validateRequired($data['name'] ?? '')) {
            self::$errors[] = 'Name is required';
        }

        if (!self::validateEmail($data['email'] ?? '')) {
            self::$errors[] = 'Valid email is required';
        }

        if (!self::validateRequired($data['subject'] ?? '')) {
            self::$errors[] = 'Subject is required';
        }

        if (!self::validateRequired($data['message'] ?? '')) {
            self::$errors[] = 'Message is required';
        }

        if (!self::validateLength($data['message'] ?? '', 10)) {
            self::$errors[] = 'Message must be at least 10 characters';
        }

        return empty(self::$errors);
    }

    /**
     * Get errors
     */
    public static function getErrors() {
        return self::$errors;
    }

    /**
     * Get first error
     */
    public static function getFirstError() {
        return !empty(self::$errors) ? self::$errors[0] : null;
    }

    /**
     * Clear errors
     */
    public static function clearErrors() {
        self::$errors = [];
    }
}
