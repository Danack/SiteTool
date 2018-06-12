<?php

declare(strict_types=1);

namespace SiteTool\Event;

use SiteTool\UrlToCheck;

class FoundUrlToFetch
{
    public $href;

    /** @var UrlToCheck */
    public $urlToCheck;
    
    public function __construct(UrlToCheck $urlToCheck)
    {
        $this->urlToCheck = $urlToCheck;
    }
}
