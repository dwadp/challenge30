<?php

use Core\Registry;

if (!function_exists('app')) {
    /**
     * Get the application instance
     *
     * @return Core\Application
     */
    function app() {
        return Registry::get('app');
    }
}

if (!function_exists('validator')) {
    /**
     * Get the validator instance
     *
     * @return Core\Validator\Validator
     */
    function validator() {
        return app()->get('validator');
    }
}

if (!function_exists('url')) {
    /**
     * Get url instance
     *
     * @return Core\Utility\Url
     */
    function url() {
        return app()->get('url');
    }
}

if (!function_exists('request')) {
    /**
     * Get request instance
     *
     * @return Core\Http\Request
     */
    function request() {
        return app()->get('request');
    }
}

if (!function_exists('config')) {
    /**
     * Get config instance
     *
     * @return Core\Config\Config
     */
    function config() {
        return app()->get('config');
    }
}

if (!function_exists('now')) {
    /**
     * Create a datetime object with default format of Y-m-d H:i:s
     *
     * @param   string $format
     * @return  string|DateTime
     */
    function now($format = '') {
        $now = new DateTime();

        if ($format) {
            return $now->format($format);
        }

        return $now;
    }
}

if (!function_exists('e')) {
    /**
     * Escape all malicious html tags or script
     *
     * @param   string $value
     * @return  string
     */
    function e($value, $type = 'html') {
        $sanitization   = app()->get('sanitization');
        $handler        = call_user_func([$sanitization, $type], null);

        return $handler->sanitize($value);
    }
}