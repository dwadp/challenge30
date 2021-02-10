<?php

namespace Core\View;

use Core\Application;
use Exception;

class View
{
    private $app;

    /**
     * The config instance
     *
     * @var App\Core\Config
     */
    private $config;

    /**
     * Views path
     *
     * @var string
     */
    private $viewsPath = '';

    /**
     * The dependencies list
     *
     * @var array
     */
    private $dependencies = [];

    public function __construct(Application $app)
    {
        $this->app          = $app;
        $this->config       = $app->get('config');
        $this->viewsPath    = $this->config->get('view.path');
    }

    /**
     * Render a given view and provide it with the given data
     *
     * @param string    $path
     * @param array     $payload
     * @return void
     */
    public function render($path, $payload = [])
    {
        require_once $this->app->makePath('vendor/autoload.php');

        $viewPath   = $this->getViewPath($path);
        $data       = $this->combinePayload($payload);

        extract($data);

        require_once $viewPath;
    }

    /**
     * Get the view path
     *
     * @param string $path
     * @return void
     */
    private function getViewPath($path)
    {
        $trimmedPath    = trim($path, '/');
        $trimmedPath = $this->viewsPath . '/' . $trimmedPath;
        $viewPath       = $this->app->makePath($trimmedPath);

        if (!file_exists($viewPath)) {
            throw new Exception("View \"{$trimmedPath}\" cannot be found");
        }

        return $viewPath;
    }

    /**
     * Combine all dependencies with the given payload
     *
     * @param array $payload
     * @return array
     */
    private function combinePayload($payload)
    {
        $combined = [];

        foreach ($this->dependencies as $key => $dependency) {
            $combined[$key] = $dependency;
        }

        if (!is_array($payload)) {
            return $combined;
        }

        foreach ($payload as $key => $data) {
            // The value should be an associative array
            // If the given payload is numerical array, it should be skipped
            if (is_numeric($key)) {
                continue;
            }

            $combined[$key] = $data;
        }

        return $combined;
    }
}