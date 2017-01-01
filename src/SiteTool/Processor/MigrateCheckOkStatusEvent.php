<?php

namespace SiteTool\Processor;

use Zend\EventManager\Event;
use SiteTool\Processor\MigrateCheckOkStatus;
use SiteTool\EventManager;

class MigrateCheckOkStatusZendEvent
{
    /** @var MigrateCheckOkStatus  */
    private $migrateCheckOkStatus;
    
    public function __construct(
        EventManager $eventManager,
        MigrateCheckOkStatus $migrateCheckOkStatus
    ) {
        $eventManager->attachEvent(SiteChecker::RESPONSE_RECEIVED, [$this, 'checkStatusEvent']);
        $this->migrateCheckOkStatus = $migrateCheckOkStatus;
    }

    public function checkStatusEvent(Event $event)
    {
        list($response, $fullURL) = $event->getParams();
        $this->migrateCheckOkStatus->checkStatus($response, $fullURL);
    }
}
