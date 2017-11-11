<?php

namespace SiteTool\Event;

use SiteTool\UrlToCheck;

class FoundUrl
{
    public $href;

    /** @var UrlToCheck */
    public $urlToCheck;
    
    public function __construct($href, UrlToCheck $urlToCheck)
    {
        $this->href = $href;
        $this->urlToCheck = $urlToCheck;
    }
}
