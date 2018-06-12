<?php

declare(strict_types=1);

namespace SiteTool\Event;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;
use SiteTool\Event\ResponseReceived;

class ResponseOk
{
    /** @var ResponseReceived */
    public $responseReceived;

    /** @var UrlToCheck */
    public $urlToCheck;
    
    public function __construct(ResponseReceived $responseReceived, UrlToCheck $urlToCheck)
    {
        $this->responseReceived = $responseReceived;
        $this->urlToCheck = $urlToCheck;
    }
}
