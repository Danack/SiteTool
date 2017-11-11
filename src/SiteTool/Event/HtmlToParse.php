<?php

namespace SiteTool\Event;

use SiteTool\UrlToCheck;
use Amp\Artax\Response;

class HtmlToParse
{
    /** @var Response  */
    public $response;
    
    /** @var UrlToCheck  */
    public $urlToCheck;

    public function __construct(UrlToCheck $urlToCheck, Response $response)
    {
        $this->response = $response;
        $this->urlToCheck = $urlToCheck;
    }
}
