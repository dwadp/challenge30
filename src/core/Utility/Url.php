<?php

namespace Core\Utility;

use Core\Registry;

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
        $protocol       = ($_SERVER['REQUEST_SCHEME'] === 'https') ? 'https://' : 'http://';
        $url            = $protocol . $host;

        // Only when the app accessed via localhost,
        // then we need to append the root folder of the app
        if (($host === 'localhost') || 
            ($host === '127.0.0.1')) {
            $url .= '/' . $this->getRootFolder();
        }

        return $url;
    }

    /**
     * Get the current project root folder name
     *
     * @return string
     */
    private function getRootFolder()
    {
        $trimmedDocumentRoot    = trim($_SERVER['SCRIPT_FILENAME'], '/');
        $segments               = explode('/', $trimmedDocumentRoot);
        $segmentsLastIndex      = count($segments) - 1;
        $projectRoot            = '';

        // Loop from the last item
        for ($index = $segmentsLastIndex; $index > 0; $index--) {
            $segment = $segments[$index];

            // If the current segment is file then skip it
            if (is_file($segment)) {
                continue;
            }

            $projectRoot = $segment;
            break;
        }

        return $projectRoot;
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