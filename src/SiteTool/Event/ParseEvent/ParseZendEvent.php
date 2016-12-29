<?php

namespace SiteTool\Event\ParseEvent;

use SiteTool\Event\ParseEvent;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use SiteTool\Processor\LinkFindingParser;
use SiteTool\Event\Data\FoundUrlEventData;
use SiteTool\EventManager;

class ParseZendEvent implements ParseEvent
{
    /** @var LinkFindingParser  */
    private $linkFindingParser;
    
    /** @var callable */
    private $foundUrlEventTrigger;

    private $switchName = "Parse the HTML";
    
    public function __construct(
        EventManager $eventManager,
        LinkFindingParser $linkFindingParser,
        $htmlReceivedEvent,
        $foundUrlEvent
    ) {
        $eventManager->attach($htmlReceivedEvent, [$this, 'parseResponseEvent'], $this->switchName);
        $this->foundUrlEventTrigger = $eventManager->createTrigger($foundUrlEvent, $this->switchName);

        //$this->foundUrlEvent = $foundUrlEvent;
        $this->linkFindingParser = $linkFindingParser;
    }

    /**
     * @param Event $e
     */
    public function parseResponseEvent(Event $e)
    {
        $params = $e->getParams();
        $urlToCheck = $params[0];
        $responseBody = $params[1];

        $this->linkFindingParser->parseResponse($urlToCheck, $responseBody, $this);
    }

    function foundUrlEvent($href, UrlToCheck $urlToCheck)
    {
        $foundUrlEventData = new FoundUrlEventData($href, $urlToCheck);
        $fn = $this->foundUrlEventTrigger;
        $fn([$foundUrlEventData]);
    }
}
