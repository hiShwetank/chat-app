<?php

if (!function_exists('base_url')) {
    /**
     * Generate a base URL for the application
     * 
     * @param string $path Optional path to append
     * @return string Full URL
     */
    function base_url($path = '') {
        // Prioritize environment variable, fallback to default
        $baseUrl = $_ENV['BASE_URL'] ?? $_ENV['APP_URL'] ?? 'http://localhost:8000';
        
        // Ensure no double slashes when joining
        $baseUrl = rtrim($baseUrl, '/');
        $path = ltrim($path, '/');
        
        return $path ? "{$baseUrl}/{$path}" : $baseUrl;
    }
}

if (!function_exists('asset_url')) {
    /**
     * Generate URL for static assets
     * 
     * @param string $path Path to the asset
     * @return string Complete asset URL
     */
    function asset_url($path = '') {
        return base_url("assets/{$path}");
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage directory
     * 
     * @param string $path Optional subdirectory or file
     * @return string Complete storage path
     */
    function storage_path($path = '') {
        $storagePath = realpath(__DIR__ . '/../../storage');
        return $path ? "{$storagePath}/{$path}" : $storagePath;
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the path to the config directory
     * 
     * @param string $path Optional config file
     * @return string Complete config path
     */
    function config_path($path = '') {
        $configPath = realpath(__DIR__ . '/../../config');
        return $path ? "{$configPath}/{$path}" : $configPath;
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public directory
     * 
     * @param string $path Optional file or subdirectory
     * @return string Complete public path
     */
    function public_path($path = '') {
        $publicPath = realpath(__DIR__ . '/../../public');
        return $path ? "{$publicPath}/{$path}" : $publicPath;
    }
}

if (!function_exists('env')) {
    /**
     * Get an environment variable with an optional default
     * 
     * @param string $key Environment variable key
     * @param mixed $default Default value if not found
     * @return mixed Environment variable value
     */
    function env($key, $default = null) {
        $value = $_ENV[$key] ?? $default;
        
        // Convert string boolean values
        if (is_string($value)) {
            return match(strtolower($value)) {
                'true' => true,
                'false' => false,
                'null' => null,
                default => $value
            };
        }
        
        return $value;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die - output a variable and terminate script
     * 
     * @param mixed $var Variable to dump
     */
    function dd(...$vars) {
        foreach ($vars as $var) {
            echo '<pre>';
            print_r($var);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize user input
     * 
     * @param string $input Input to sanitize
     * @return string Sanitized input
     */
    function sanitize_input($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
}

if (!function_exists('generate_uuid')) {
    /**
     * Generate a unique identifier
     * 
     * @return string UUID
     */
    function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

if (!function_exists('get_current_timezone')) {
    /**
     * Get the current timezone based on user's location or default
     * 
     * @return string Timezone identifier
     */
    function get_current_timezone() {
        // Priority: 1. User setting, 2. ENV, 3. Server default, 4. Fallback
        $userTimezone = $_SESSION['timezone'] ?? null;
        $envTimezone = $_ENV['APP_TIMEZONE'] ?? null;
        
        // List of common timezones for India
        $indianTimezones = [
            'Asia/Kolkata',     // Most common
            'Asia/Bangalore',
            'Asia/Chennai',
            'Asia/Mumbai',
            'Asia/New_Delhi'
        ];

        // Determine timezone
        $timezone = $userTimezone 
            ?? $envTimezone 
            ?? date_default_timezone_get() 
            ?? 'Asia/Kolkata';

        // Validate timezone
        try {
            new DateTimeZone($timezone);
        } catch (Exception $e) {
            $timezone = 'Asia/Kolkata';
        }

        return $timezone;
    }
}

if (!function_exists('format_localized_date')) {
    /**
     * Format date according to localization settings
     * 
     * @param mixed $date Date to format
     * @param string $format Optional date format
     * @param string $locale Optional locale
     * @return string Formatted date
     */
    function format_localized_date($date = null, $format = 'full', $locale = 'en_IN') {
        // Normalize date
        $date = $date ?? new DateTime();
        if (is_string($date)) {
            $date = new DateTime($date);
        }

        // Set timezone
        $date->setTimezone(new DateTimeZone(get_current_timezone()));

        // Predefined formats
        $formats = [
            'full' => 'D, d M Y H:i:s',
            'short' => 'd/m/Y',
            'long' => 'l, d F Y',
            'time' => 'H:i:s',
            'datetime' => 'd M Y H:i:s'
        ];

        // Select format
        $selectedFormat = $formats[$format] ?? $formats['full'];

        // Set locale
        setlocale(LC_TIME, $locale);

        return $date->format($selectedFormat);
    }
}

if (!function_exists('get_country_code')) {
    /**
     * Get the country code based on current settings
     * 
     * @return string Country code (default 'IN')
     */
    function get_country_code() {
        // Priority: 1. User setting, 2. ENV, 3. Fallback
        return $_SESSION['country_code'] 
            ?? $_ENV['APP_COUNTRY_CODE'] 
            ?? 'IN';
    }
}

if (!function_exists('convert_to_local_time')) {
    /**
     * Convert a timestamp to local time
     * 
     * @param mixed $timestamp Timestamp to convert
     * @return DateTime Local time
     */
    function convert_to_local_time($timestamp = null) {
        $timestamp = $timestamp ?? time();
        
        // Create DateTime object in current timezone
        $dateTime = new DateTime('@' . $timestamp);
        $dateTime->setTimezone(new DateTimeZone(get_current_timezone()));
        
        return $dateTime;
    }
}
