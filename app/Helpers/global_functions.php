<?php

if (!function_exists('base_url')) {
    /**
     * Generate a base URL for the application
     * 
     * @param string $path Optional path to append
     * @return string Complete URL
     */
    function base_url($path = '') {
        // Get the base URL from environment or default
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        
        // Trim any trailing slashes from base URL and path
        $baseUrl = rtrim($baseUrl, '/');
        $path = ltrim($path, '/');
        
        // Combine base URL and path
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
