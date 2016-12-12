<?php

namespace SiteTool\Event\ContentTypeEvent;

use SiteTool\Event\ContentTypeEvent;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use SiteTool\SiteChecker;
use SiteTool\UrlToCheck;
use Amp\Artax\Response;
use SiteTool\Processor\ContentTypeEventList;

class ContentTypeZendEvent implements ContentTypeEvent 
{
    /** @var ContentTypeEventList  */
    private $contentTypeEventList;
    
    /** @var EventManager  */
    private $eventManager;

    public function __construct(
        EventManager $eventManager,
        ContentTypeEventList $contentTypeEventList
    ) {
        $this->eventManager = $eventManager;
        $eventManager->attach(SiteChecker::RESPONSE_OK, [$this, 'responseOkEvent']);
        $this->contentTypeEventList = $contentTypeEventList;
    }
    
    function htmlReceived(Response $response, UrlToCheck $urlToCheck)
    {
        $this->eventManager->trigger(SiteChecker::HTML_RECEIVED, null, [$urlToCheck, $response->getBody()]);
    }

    function responseOkEvent(Event $event)
    {
        $params = $event->getParams();
        $response = $params[0];
        $urlToCheck = $params[1];

        $this->contentTypeEventList->triggerEventForContent($response, $urlToCheck, $this);
    }
}

