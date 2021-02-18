<?php

use Core\Registry;

if (!function_exists('app')) {
    /**
     * Get the application instance
     *
     * @return Core\Application
     */
    function app() {
        return Registry::get('app');
    }
}

if (!function_exists('validator')) {
    /**
     * Get the validator instance
     *
     * @return Core\Validator\Validator
     */
    function validator() {
        return app()->get('validator');
    }
}

if (!function_exists('url')) {
    /**
     * Get url instance
     *
     * @return Core\Utility\Url
     */
    function url() {
        return app()->get('url');
    }
}

if (!function_exists('request')) {
    /**
     * Get request instance
     *
     * @return Core\Http\Request
     */
    function request() {
        return app()->get('request');
    }
}

if (!function_exists('config')) {
    /**
     * Get config instance
     *
     * @return Core\Config\Config
     */
    function config() {
        return app()->get('config');
    }
}

if (!function_exists('now')) {
    /**
     * Create a datetime object with default format of Y-m-d H:i:s
     *
     * @param   string $format
     * @return  string|DateTime
     */
    function now($format = '') {
        $now = new DateTime();

        if ($format) {
            return $now->format($format);
        }

        return $now;
    }
}

if (!function_exists('e')) {
    /**
     * Escape all malicious html tags or script
     *
     * @param   string $value
     * @return  string
     */
    function e($value) {
        // List of html events attributes
        // Reference: https://www.w3schools.com/tags/ref_eventattributes.asp
        $windowEvents       = [
            'onafterprint',
            'onbeforeprint',
            'onbeforeunload',
            'onerror',
            'onhashchange',
            'onload',
            'onmessage',
            'onoffline',
            'ononline',
            'onpagehide',
            'onpageshow',
            'onpopstate',
            'onresize',
            'onstorage',
            'onunload'
        ];

        $formEvents             = [
            'onblur',
            'onchange',
            'oncontextmenu',
            'onfocus',
            'oninput',
            'oninvalid',
            'onreset',
            'onsearch',
            'onselect',
            'onsubmit'
        ];

        $keyboardEvents         = [
            'onkeydown',
            'onkeypress',
            'onkeyup'
        ];

        $mouseEvents            = [
            'onclick',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onwheel'
        ];

        $dragEvents             = [
            'ondrag',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'onscroll'
        ];

        $clipboardEvents        = [
            'oncopy',
            'oncut',
            'onpaste'
        ];

        $mediaEvents            = [
            'onabort',
            'oncanplay',
            'oncanplaythrough',
            'oncuechange',
            'ondurationchange',
            'onemptied',
            'onended',
            'onerror',
            'onloadeddata',
            'onloadedmetadata',
            'onloadstart',
            'onpause',
            'onplay',
            'onplaying',
            'onprogress',
            'onratechange',
            'onseeked',
            'onseeking',
            'onstalled',
            'onsuspend',
            'ontimeupdate',
            'onvolumechange',
            'onwaiting'
        ];

        $miscEvents             = [ 'ontoggle' ];

        // List of html tags that are consider as dangerous
        $tags                   = [
            'script',
            'style',
            'object',
            'embed',
            'link'
        ];

        $events = array_merge(
            $windowEvents,
            $formEvents,
            $keyboardEvents,
            $mouseEvents,
            $dragEvents,
            $clipboardEvents,
            $mediaEvents,
            $miscEvents
        );

        $sanitized = '';

        // Remove all html DOM events
        foreach ($events as $key => $event) {
            $pattern    = '/(<[^>]+)*?' . $event . '=".*?"/im';
            $sanitized  = preg_replace(
                $pattern,
                '$1',
                ($key === 0) ? $value : $sanitized
            );
        }

        // Find html embedded script and if it's not a valid url, then remove it
        // Example: <a href="javascript:alert('Alert')">Link</a>
        $sanitized = preg_replace(
            '/(href|src|background|dynsrc|lowsrc)\=.(?!https|http|mailto).*?:.*?((?=\>)|(?=\<)|(?<=\"))/im', 
            '$1', 
            $sanitized
        );

        // Replace the listed tags with more safe characters & symbols
        foreach ($tags as $tag) {
            $pattern = '/(<' . $tag . '.*?>([\s\S]*?)|<\/' . $tag . '>)/im';

            // Any tags matched with the listed sanitized tags should be sanitized
            // including a non valid html tag
            if ((preg_match($pattern, $sanitized)) ||
                (!preg_match("/<(\"[^\"]*\"|'[^']*'|[^'\">])*>/im", $sanitized))) {
                return htmlspecialchars($sanitized, ENT_QUOTES);
            }
        }

        return $sanitized;
    }
}