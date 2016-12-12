<?php

namespace SiteTool\Event;

use SiteTool\UrlToCheck;
use Zend\EventManager\Event;

interface RulesEvent
{
    public function foundUrlEvent(Event $event);

    public function skippingLink($href, $host);

    function foundUrlToFollow(UrlToCheck $urlToCheck);
}