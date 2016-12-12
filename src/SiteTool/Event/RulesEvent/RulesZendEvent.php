<?php


namespace SiteTool\Event\RulesEvent;

use SiteTool\Event\RulesEvent;
use SiteTool\Processor\Rules;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

class RulesZendEvent implements RulesEvent
{
    private $rules;
    
    /** @var \Zend\EventManager\EventManager */
    private $eventManager;
    
    private $skippingLinkEvent;
    
    private $foundUrlToFollowEvent;
    
    public function __construct(
        EventManager $eventManager,
        Rules $rules,
        $foundUrlEvent, $skippingLinkEvent, $foundUrlToFollowEvent
    ) {
        $this->rules = $rules;
        $this->eventManager = $eventManager;
        $this->eventManager->attach($foundUrlEvent, [$this, 'foundUrlEvent']);

        $this->skippingLinkEvent = $skippingLinkEvent;
        $this->foundUrlToFollowEvent = $foundUrlToFollowEvent;
    }

    public function foundUrlEvent(Event $event)
    {
        $params = $event->getParams();
        $foundUrlEventData = $params[0];
        $this->rules->getUrlToCheck($foundUrlEventData, $this);
    }

    public function skippingLink($href, $host)
    {
        $this->eventManager->trigger(
            $this->skippingLinkEvent,
            null,
            [$href, $host]
        );
    }

    function foundUrlToFollow(UrlToCheck $urlToCheck)
    {
        $this->eventManager->trigger($this->foundUrlToFollowEvent, null, [$urlToCheck]);
    }
}
