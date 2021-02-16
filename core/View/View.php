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

    public function __construct(Application $app)
    {
        $this->app          = $app;
        $this->config       = $app->get('config');
        $this->viewsPath    = $this->config->get('view.path');
    }

    /**
     * Render a given view and provide it with the given data
     *
     * @param   string    $path
     * @param   array     $payload
     * @return  void
     */
    public function render($path, $data = [])
    {
        require_once $this->app->makePath('vendor/autoload.php');

        $viewPath   = $this->getViewPath($path);

        extract($data);

        require_once $viewPath;
    }

    /**
     * Get the view path
     *
     * @param   string $path
     * @return  void
     */
    private function getViewPath($path)
    {
        $trimmedPath    = trim($path, '/');
        $trimmedPath    = $this->viewsPath . '/' . $trimmedPath;
        $viewPath       = $this->app->makePath($trimmedPath);

        if (!file_exists($viewPath)) {
            throw new Exception("View \"{$trimmedPath}\" cannot be found");
        }

        return $viewPath;
    }
}