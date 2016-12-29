<?php

namespace SiteTool\Event\ResponseOkEvent;

use SiteTool\Event\RulesEvent;
use SiteTool\Processor\Rules;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use SiteTool\Event\ResponseOkEvent;
use SiteTool\Processor\ResponseValid;
use Amp\Artax\Response;
use SiteTool\SiteChecker;
use SiteTool\EventManager;

class ResponseOkZendEvent implements ResponseOkEvent
{
    /** @var  \callable */
    private $responseOkTrigger;

    
    private $switchName = "Is the response OK?";
    
    public function __construct(
        EventManager $eventManager,
        ResponseValid $responseValid
    ) {
        $this->responseValid = $responseValid;
        $eventManager->attach(SiteChecker::RESPONSE_RECEIVED, [$this, 'resultFetchedEvent'], $this->switchName);
        $this->responseOkTrigger = $eventManager->createTrigger(SiteChecker::RESPONSE_OK, $this->switchName);
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
        $fn = $this->responseOkTrigger;
        $fn([$response, $urlToCheck]);
    }
}
