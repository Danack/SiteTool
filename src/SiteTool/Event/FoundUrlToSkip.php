<?php

declare(strict_types=1);

namespace SiteTool\Event;

use SiteTool\UrlToCheck;

class FoundUrlToSkip
{
    /** @var string */
    public $href;
    
    /** @var string  */
    public $host;

    public function __construct($href, $host)
    {
        $this->href = $href;
        $this->host = $host;
    }
}
