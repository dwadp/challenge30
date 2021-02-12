<?php

namespace Core\Config;

class Config
{
    /**
     * All configurations
     *
     * @var array
     */
    private static $configs = [];

    /**
     * Configuration options
     *
     * @var array
     */
    private static $options = [];

    public function __construct($options = [])
    {
        if (count($options) > 0) {
            self::$options = $options;
        }

        $this->loadConfigurationFiles();
    }

    /**
     * Load all configuration files inside the config path
     *
     * @return void
     */
    private function loadConfigurationFiles()
    {
        $options = self::$options;

        foreach ($options['files'] as $name) {
            $path   = $options['path'] . '/' . $name . '.php';

            $value  = include_once $path;

            $this->add($name, $value);
        }
    }

    /**
     * Add new configuration data
     *
     * @param   string  $key
     * @param   array   $configs
     * @return  void
     */
    public function add($key, $configs)
    {
        self::$configs[$key] = $configs;
    }

    /**
     * Check if configuration exists by a specific key
     *
     * @param   string  $key
     * @return  boolean
     */
    public function has($key)
    {
        return array_key_exists($key, self::$configs);
    }

    /**
     * Get configuration value by a specific key
     *
     * @param   string $key
     * @return  mixed
     */
    public function get($key, $default = '')
    {
        if ($this->isNestedKey($key)) {
            return $this->getNested($key, '', [], $default);
        }

        if (!$this->has($key)) {
            return null;
        }

        $config = self::$configs[$key];

        if ($this->shouldUseDefault($config, $default)) {
            return $default;
        }

        return $config;
    }

    /**
     * Determine if should use default instead of the value
     *
     * @param   mixed $value
     * @param   mixed $default
     * @return  boolean
     */
    private function shouldUseDefault($value, $default)
    {
        if ((is_bool($value)) && 
            ($value === null) &&
            ($default)) {
            return true;
        }

        if ((is_string($value)) &&
            ($value === '') &&
            ($default !== '')) {
            return true;
        }

        return false;
    }

    /**
     * Check is the given configuration key is nested
     * Example: app.level1.level2
     *
     * @param   string $key
     * @return  boolean
     */
    private function isNestedKey($key)
    {
        return preg_match('/\./', $key) > 0;
    }

    /**
     * Get the actual configuration value with a nested key
     *
     * @param   string  $key
     * @param   string  $parentKey
     * @param   array   $configs
     * @return  mixed
     */
    private function getNested($key, $parentKey = '', $configs = [], $default = '')
    {
        $keys = explode('.', $key);

        if (count($keys) === 0) {
            return null;
        }

        if ($parentKey === '') {
            $parentKey = $keys[0];
        }
        
        if (count($configs) === 0) {
            if (!$this->has($parentKey)) {
                return null;
            }

            $configs = self::$configs[$parentKey];
        }

        foreach ($keys as $value) {
            // Skip the parent key
            if (($value === $parentKey) ||
                !array_key_exists($value, $configs)) {
                continue;
            }

            $config         = $configs[$value];
            $remainingKeys  = $this->getRemainingKeys($parentKey, $keys);

            // If only one key left, just return the current config value
            if (count($remainingKeys) < 2) {
                if ($this->shouldUseDefault($config, $default)) {
                    return $default;
                }

                return $config;
            }

            if (is_array($config)) {
                $nestedKey = $this->getNestedKey($parentKey, $keys);

                return $this->getNested($nestedKey, $value, $config, $default);
            }

            if ($this->shouldUseDefault($config, $default)) {
                return $default;
            }

            return $config;
        }

        return null;
    }

    /**
     * Get all keys without their parent key
     *
     * @param   string  $parentKey
     * @param   array   $keys
     * @return  array
     */
    private function getKeysWithoutParent($parentKey, $keys)
    {
        return array_filter($keys, function($key) use ($parentKey) {
            return ($key !== $parentKey);
        });
    }

    /**
     * Rebuild nested configuration key but skip the parent key
     * Example Parent Key: app.level1.level2
     * Example Result: level1.level2
     *
     * @param   string  $parentKey
     * @param   array   $keys
     * @return  string
     */
    private function getNestedKey($parentKey, $keys)
    {
        return implode('.', $this->getKeysWithoutParent($parentKey, $keys));
    }

    /**
     * Get the remaining keys from nested key without a parent key
     *
     * @param   string  $parentKey
     * @param   array   $keys
     * @return  array
     */
    private function getRemainingKeys($parentKey, $keys)
    {
        return $this->getKeysWithoutParent($parentKey, $keys);
    }
}