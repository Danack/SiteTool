<?php

namespace SiteTool\Event\Data;

use SiteTool\UrlToCheck;

class FoundUrlEventData
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
