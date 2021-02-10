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
     * Get url instance
     *
     * @return Core\Http\Request
     */
    function request() {
        return app()->get('request');
    }
}

if (!function_exists('config')) {
    /**
     * Get url instance
     *
     * @return Core\Config\Config
     */
    function config() {
        return app()->get('config');
    }
}