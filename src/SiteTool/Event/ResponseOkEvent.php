<?php


namespace SiteTool\Event;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;

interface ResponseOkEvent
{
    public function responseOk(Response $response, UrlToCheck $urlToCheck);
}
