<?php

namespace App\Controllers;

use App\Core\Registry;
use App\Core\Url;

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
     * The request instance
     *
     * @var App\Core\Request
     */
    protected $request;

    /**
     * The url instance
     *
     * @var App\Core\Url
     */
    protected $url;

    /**
     * The view instance
     *
     * @var App\Core\View
     */
    protected $view;

    public function __construct()
    {
        $this->config       = Registry::get('config');
        $this->validator    = Registry::get('validator');
        $this->request      = Registry::get('request');
        $this->url          = Registry::get('url');
        $this->view         = Registry::get('view');

        $this->initializeController();
    }

    /**
     * Initialize the base controller
     *
     * @return void
     */
    private function initializeController()
    {
        // Capture all incoming request
        $this->request->capture();

        // Set dependencies so the view can use it
        $this->view->setDependencies($this->getDependencies());
    }

    /**
     * Define all dependencies
     * This dependencies will be provided to all views
     *
     * @return array
     */
    private function getDependencies()
    {
        return [
            'validator' => $this->validator,
            'request'   => $this->request,
            'url'       => $this->url
        ];
    }

    /**
     * Redirect to intended page
     *
     * @param string $location
     * @return void
     */
    protected function redirect($location)
    {
        $url = $this->url->make($location);

        header("Location: {$url}");
    }
}