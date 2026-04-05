<?php
/**
 * Input Validation and Sanitization Helper
 * 
 * Simple functions to validate and sanitize user input.
 * Always validate input before using it in queries or displaying it.
 */

/**
 * Sanitize a string - removes HTML tags and trims whitespace
 * 
 * @param string $input The input string
 * @param int $maxLength Maximum allowed length (0 = no limit)
 * @return string Sanitized string
 */
function sanitizeString($input, $maxLength = 0) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    if ($maxLength > 0 && strlen($input) > $maxLength) {
        $input = substr($input, 0, $maxLength);
    }
    
    return $input;
}

/**
 * Sanitize and validate an email address
 * 
 * @param string $email The email to validate
 * @return string|false Sanitized email or false if invalid
 */
function sanitizeEmail($email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    
    return false;
}

/**
 * Validate and sanitize an integer
 * 
 * @param mixed $input The input to validate
 * @param int $min Minimum value (optional)
 * @param int $max Maximum value (optional)
 * @return int|false Integer value or false if invalid
 */
function sanitizeInt($input, $min = null, $max = null) {
    $value = filter_var($input, FILTER_VALIDATE_INT);
    
    if ($value === false) {
        return false;
    }
    
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return $value;
}

/**
 * Validate a date string (Y-m-d format)
 * 
 * @param string $date The date string to validate
 * @return string|false Valid date string or false if invalid
 */
function validateDate($date) {
    $date = trim($date);
    
    // Check format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    
    // Check if it's a valid date
    $parts = explode('-', $date);
    if (!checkdate($parts[1], $parts[2], $parts[0])) {
        return false;
    }
    
    return $date;
}

/**
 * Sanitize and validate a float/decimal number
 * 
 * @param mixed $input The input to validate
 * @param float $min Minimum value (optional)
 * @param float $max Maximum value (optional)
 * @return float|false Float value or false if invalid
 */
function sanitizeFloat($input, $min = null, $max = null) {
    $value = filter_var($input, FILTER_VALIDATE_FLOAT);
    
    if ($value === false) {
        return false;
    }
    
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return $value;
}

/**
 * Check if a value is in an allowed list (whitelist validation)
 * 
 * @param mixed $value The value to check
 * @param array $allowedValues Array of allowed values
 * @return bool True if valid, false otherwise
 */
function isAllowedValue($value, $allowedValues) {
    return in_array($value, $allowedValues, true);
}

/**
 * Sanitize a URL
 * 
 * @param string $url The URL to sanitize
 * @return string|false Sanitized URL or false if invalid
 */
function sanitizeUrl($url) {
    $url = trim($url);
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    
    return false;
}
?>
