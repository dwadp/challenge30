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
     * @param string $location
     * @return void
     */
    protected function redirect($location)
    {
        $url = url()->make($location);

        header("Location: {$url}");
    }

    public function __get($name)
    {
        return app()->get($name);
    }
}