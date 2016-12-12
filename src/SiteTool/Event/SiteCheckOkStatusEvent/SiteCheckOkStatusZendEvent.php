<?php


namespace SiteTool\Event\SiteCheckOkStatusEvent;

use SiteTool\Event\SiteCheckOkStatusEvent;
use SiteTool\SiteChecker;
use Zend\EventManager\EventManager;
use SiteTool\Processor\SiteCheckOkStatus;
use Zend\EventManager\Event;



class SiteCheckOkStatusZendEvent implements SiteCheckOkStatusEvent
{
    /** @var SiteCheckOkStatus */
    private $siteCheckOkStatus;

    /** @var   */
    private $eventManager;
    
    public function __construct(
        EventManager $eventManager,
        SiteCheckOkStatus $siteCheckOkStatus
    ) {
        $this->eventManager = $eventManager;
        $eventManager->attach(SiteChecker::RESPONSE_RECEIVED, [$this, 'checkStatusEvent']);
        $this->siteCheckOkStatus = $siteCheckOkStatus;
    }

    public function checkStatusEvent(Event $event)
    {
        list($response, $fullURL) = $event->getParams();
        $this->siteCheckOkStatus->checkStatus($response, $fullURL);
    }

    public function statusIsOk()
    {
        $this->eventManager
    }
}
