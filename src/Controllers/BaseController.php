<?php

namespace App\Controllers;

use App\Core\Config;
use App\Core\Registry;
use App\Core\Validator;

class BaseController
{
    /**
     * The config instance
     *
     * @var App\Core\Config
     */
    protected $config;

    /**
     * The validator instance
     *
     * @var App\Core\Validator
     */
    protected $validator;

    /**
     * Views path
     *
     * @var string
     */
    protected $viewsPath;

    public function __construct()
    {
        $this->config       = Registry::get('config');
        $this->validator    = Registry::get('validator');
        $this->viewsPath    = $this->config->get('view.path');
    }

    /**
     * Get all dependencies
     *
     * @return void
     */
    private function getDependencies()
    {
        return [
            'config'    => $this->config,
            'validator' => $this->validator
        ];
    }

    /**
     * Load a view and pass some data to it
     *
     * @param string $path
     * @param array $data
     * @return void
     */
    protected function view($path, $data = [])
    {
        extract($this->getDependencies());
        extract($data);

        require_once $this->viewsPath . '/' . $path;
    }

    /**
     * Redirect to intended page
     *
     * @param string $location
     * @return void
     */
    protected function redirect($location)
    {
        $intended   = $location === "/" || $location === "" ? false : true;
        $url        = $this->config->get('app.baseUrl');

        if ($intended) {
            $url .= "/{$location}";
        }

        header("Location: {$url}");
    }
}