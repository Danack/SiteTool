<?php


namespace SiteTool\Event\RulesEvent;

use SiteTool\Event\RulesEvent;
use SiteTool\Processor\Rules;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
//use Zend\EventManager\EventManager;
use SiteTool\EventManager;

class RulesZendEvent implements RulesEvent
{
    private $rules;

    /** @var  callable */
    private $skippingLinkTrigger;
    
    /** @var  callable */
    private $foundUrlToFollowTrigger;

    private $switchName = "Should we follow this URL?";
    
    public function __construct(
        EventManager $eventManager,
        Rules $rules,
        $foundUrlEvent, $skippingLinkEvent, $foundUrlToFollowEvent
    ) {
        $this->rules = $rules;

        $eventManager->attach($foundUrlEvent, [$this, 'foundUrlEvent'], $this->switchName);
        $this->skippingLinkTrigger =  $eventManager->createTrigger($skippingLinkEvent, $this->switchName);
        $this->foundUrlToFollowTrigger =  $eventManager->createTrigger($foundUrlToFollowEvent, $this->switchName);
    }

    public function foundUrlEvent(Event $event)
    {
        $params = $event->getParams();
        $foundUrlEventData = $params[0];
        $this->rules->getUrlToCheck($foundUrlEventData, $this);
    }

    public function skippingLink($href, $host)
    {
        $fn = $this->skippingLinkTrigger;
        $fn([$href, $host]);
    }

    function foundUrlToFollow(UrlToCheck $urlToCheck)
    {
        $fn = $this->foundUrlToFollowTrigger;
        $fn([$urlToCheck]);
    }
}
