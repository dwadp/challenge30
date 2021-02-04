<?php

namespace App\Core;

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
    private static $options = [
        'path' => 'config',
        'files' => []
    ];

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
        $config = $this;
        $options = self::$options;

        foreach ($options['files'] as $name) {
            $path = $options['path'] . '/' . $name . '.php';

            require_once $path;
        }
    }

    /**
     * Add new configuration data
     *
     * @param string $key
     * @param array $configs
     * @return void
     */
    public function add($key, $configs)
    {
        self::$configs[$key] = $configs;
    }

    /**
     * Check if configuration exists by a specific key
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        if (!array_key_exists($key, self::$configs)) {
            return false;
        }

        return true;
    }

    /**
     * Get configuration value by a specific key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if ($this->isNestedKey($key)) {
            return $this->getNested($key);
        }

        if (!$this->has($key)) {
            return null;
        }

        $config = self::$configs[$key];

        return $config;
    }

    /**
     * Check is the given configuration key is nested
     * Example: app.level1.level2
     *
     * @param string $key
     * @return boolean
     */
    private function isNestedKey($key)
    {
        $isNested = preg_match('/\./', $key);

        if ($isNested > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get the actual configuration value with a nested key
     *
     * @param string $key
     * @param string $parentKey
     * @param array $configs
     * @return mixed
     */
    private function getNested($key, $parentKey = '', $configs = [])
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

            $config = $configs[$value];
            $remainingKeys = $this->getRemainingKeys($parentKey, $keys);

            // If only one key left, just return the current config value
            if (count($remainingKeys) < 2) {
                return $config;
            }

            if (is_array($config)) {
                $nestedKey = $this->getNestedKey($parentKey, $keys);

                return $this->getNested($nestedKey, $value, $config);
            }

            return $config;
        }

        return null;
    }

    /**
     * Get all keys without their parent key
     *
     * @param string $parentKey
     * @param array $keys
     * @return array
     */
    private function getKeysWithoutParent($parentKey, $keys)
    {
        $filteredKeys = array_filter($keys, function($key) use ($parentKey) {
            return ($key !== $parentKey);
        });

        return $filteredKeys;
    }

    /**
     * Rebuild nested configuration key but skip the parent key
     * Example Parent Key: app.level1.level2
     * Example Result: level1.level2
     *
     * @param string $parentKey
     * @param array $keys
     * @return string
     */
    private function getNestedKey($parentKey, $keys)
    {
        $filteredKeys = $this->getKeysWithoutParent($parentKey, $keys);

        return implode('.', $filteredKeys);
    }

    /**
     * Get the remaining keys from nested key without a parent key
     *
     * @param string $parentKey
     * @param array $keys
     * @return array
     */
    private function getRemainingKeys($parentKey, $keys)
    {
        return $this->getKeysWithoutParent($parentKey, $keys);
    }
}