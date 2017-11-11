<?php


namespace SiteTool\Event;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;

class ResponseReceived
{
    public $response;
    public $urlToCheck;
    
    public function __construct(Response $response, UrlToCheck $urlToCheck)
    {
        $this->response = $response;
        $this->urlToCheck = $urlToCheck;
    }
}
