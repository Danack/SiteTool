<?php

namespace SiteTool\Event\ResponseOkEvent;

use SiteTool\Event\RulesEvent;
use SiteTool\Processor\Rules;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use SiteTool\Event\ResponseOkEvent;
use SiteTool\Processor\ResponseValid;
use Amp\Artax\Response;
use SiteTool\SiteChecker;

class ResponseOkZendEvent implements ResponseOkEvent
{
    /** @var EventManager  */
    private $eventManager;
    
    public function __construct(
        EventManager $eventManager,
        ResponseValid $responseValid
    ) {
        $this->eventManager = $eventManager;
        $this->responseValid = $responseValid;
        $eventManager->attach(SiteChecker::RESPONSE_RECEIVED, [$this, 'resultFetchedEvent']);
    }

    public function resultFetchedEvent(Event $event)
    {
        $params = $event->getParams();
        $e = $params[0];
        $response = $params[1];
        $urlToCheck = $params[2];
        $fullURL  = $params[3];

        $this->responseValid->analyzeResult(
            $e, 
            $response,
            $urlToCheck,
            $fullURL,
            $this
        );
    }

    public function responseOk(Response $response, UrlToCheck $urlToCheck)
    {
        $this->eventManager->trigger(SiteChecker::RESPONSE_OK, null, [$response, $urlToCheck]);
    }
}
