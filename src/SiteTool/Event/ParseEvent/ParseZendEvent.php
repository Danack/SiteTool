<?php

namespace SiteTool\Event\ParseEvent;

use SiteTool\Event\ParseEvent;
use SiteTool\UrlToCheck;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Processor\LinkFindingParser;
use SiteTool\Event\Data\FoundUrlEventData;

class ParseZendEvent implements ParseEvent
{
    /** @var LinkFindingParser  */
    private $linkFindingParser;
    
    public function __construct(
        EventManager $eventManager,
        LinkFindingParser $linkFindingParser,
        $htmlReceivedEvent,
        $foundUrlEvent
    ) {
        //$this->rules = $rules;
        $this->eventManager = $eventManager;
        $eventManager->attach($htmlReceivedEvent, [$this, 'parseResponseEvent']);
        $this->foundUrlEvent = $foundUrlEvent;
        
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

        $this->eventManager->trigger($this->foundUrlEvent, null, [$foundUrlEventData]);
    }
}
