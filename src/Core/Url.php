<?php

namespace App\Core;

use App\Core\Registry;

class Url
{
    /**
     * The config instance
     *
     * @var App\Core\Config
     */
    private $config;

    public function __construct()
    {
        $this->config = Registry::get('config');
    }

    /**
     * Make a full url based on the 'baseUrl' that has been set in configuration file
     *
     * @param string $path
     * @return string
     */
    public function make($path = '')
    {
        return $this->buildFullUrl($path);
    }

    /**
     * Build the full url with a path
     *
     * @param string    $path
     * @return string
     */
    private function buildFullUrl($path = '')
    {
        $baseUrl    = trim($this->config->get('app.baseUrl'), '/');
        $url        = $this->getRequestUrl($baseUrl);

        return $this->appendPath($url, $path);
    }

    /**
     * Get request url without the request path
     *
     * @param string $baseUrl
     * @return string
     */
    private function getRequestUrl($baseUrl = '')
    {
        if ($baseUrl !== '') {
            return $baseUrl;
        }

        $host           = $_SERVER['HTTP_HOST'];
        $requestUri     = trim($_SERVER['REQUEST_URI'], '/');
        $segments       = explode('/', $requestUri);
        $protocol       = ($_SERVER['REQUEST_SCHEME'] === 'https') ? 'https://' : 'http://';

        $url            = $protocol . $host;

        if ((count($segments) > 0) &&
            ($segments[0] !== '')) {
            $url .= '/' . $segments[0];
        }

        return $url;
    }

    /**
     * Append path to the given url
     *
     * @param string $url
     * @param string $path
     * @return string
     */
    private function appendPath($url, $path = '')
    {
        $trimmedPath = trim($path, '/');

        if ($trimmedPath === '') {
            return $url;
        }

        return $url . '/' . $trimmedPath;
    }
}