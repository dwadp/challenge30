<?php

namespace Core\Utility\Sanitization;

class HtmlSanitization
{
    /**
     * List of html DOM events attributes
     * Reference: https://www.w3schools.com/tags/ref_eventattributes.asp
     *
     * @var array
     */
    protected $domEvents = [
        'window'    => [
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
        ],
        'form'      => [
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
        ],
        'keyboard'  => [
            'onkeydown',
            'onkeypress',
            'onkeyup'
        ],
        'mouse'     => [
            'onclick',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onwheel'
        ],
        'drag'      => [
            'ondrag',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'onscroll'
        ],
        'clipboard' => [
            'oncopy',
            'oncut',
            'onpaste'
        ],
        'media'     => [
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
        ],
        'misc'      => [
            'ontoggle'
        ]
    ];

    /**
     * List of html tags that are consider as dangerous
     *
     * @var array
     */
    protected $maliciousTags = [
        'script',
        'style',
        'object',
        'embed',
        'link'
    ];

    /**
     * Sanitize & Escape any malicious html tag or script
     *
     * @param   mixed $value
     * @return  string
     */
    public function sanitize($value)
    {
        $sanitized = '';

        // Remove all html DOM events
        foreach ($this->events() as $key => $event) {
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
        foreach ($this->maliciousTags as $tag) {
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

    /**
     * Merge all DOM events
     *
     * @return array
     */
    protected function events()
    {
        $events = [];

        foreach ($this->domEvents as $domEvent) {
            $events = array_merge($events, $domEvent);
        }

        return $events;
    }
}