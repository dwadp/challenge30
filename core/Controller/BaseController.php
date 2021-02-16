<?php

namespace Core\Controller;

class BaseController
{
    public function __construct()
    {
        // Capture all incoming request
        request()->capture();
    }

    /**
     * Redirect to intended page
     *
     * @param   string $location
     * @return  void
     */
    protected function redirect($location)
    {
        $url = url()->make($location);

        header("Location: {$url}");
    }

    /**
     * Handle when children class accessing a property
     * and automatically will get dependency from the application
     *
     * @param   string $name
     * @return  null|object
     */
    public function __get($name)
    {
        return app()->get($name);
    }
}