<?php

namespace SiteTool\Event\MigrateCheckOkStatusEvent;

//use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\SiteChecker;
use SiteTool\Event\MigrateCheckOkStatusEvent;
use SiteTool\Processor\MigrateCheckOkStatus;

use SiteTool\EventManager;


class MigrateCheckOkStatusZendEvent implements MigrateCheckOkStatusEvent
{
    /** @var MigrateCheckOkStatus  */
    private $migrateCheckOkStatus;
    
    public function __construct(
        EventManager $eventManager,
        MigrateCheckOkStatus $migrateCheckOkStatus
    ) {
        $eventManager->attach(SiteChecker::RESPONSE_RECEIVED, [$this, 'checkStatusEvent']);
        $this->migrateCheckOkStatus = $migrateCheckOkStatus;
    }

    public function checkStatusEvent(Event $event)
    {
        list($response, $fullURL) = $event->getParams();
        $this->migrateCheckOkStatus->checkStatus($response, $fullURL);
    }
}
