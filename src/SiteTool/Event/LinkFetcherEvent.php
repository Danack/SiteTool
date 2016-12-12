<?php


namespace SiteTool\Event;

use SiteTool\Event\RulesEvent;
use SiteTool\Processor\Rules;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use SiteTool\Event\ResponseOkEvent;
use SiteTool\Processor\ResponseValid;
use Amp\Artax\Response;



interface LinkFetcherEvent
{
    function resultFetched(
        \Exception $e,
        Response $response, 
        UrlToCheck $urlToCheck,
        $fullURL
    );
}
