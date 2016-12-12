<?php

namespace SiteTool;



use Auryn\Injector;
use SiteTool\CrawlerConfig;
use SiteTool\Processor\LinkFindingParser;
use SiteTool\Processor\Rules;
use SiteTool\SiteChecker;
use SiteTool\Processor\SkippingLinkWatcher;
use SiteTool\URLToCheck;
use SiteTool\Writer\StatusWriter;
use Zend\EventManager\EventManager;
use SiteTool\Processor\ContentTypeEventList;


class PluginFactory
{
    private $injector;
    
    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    public function createRules($listenEvent, $skipEvent, $foundEvent)
    { 
        return $this->injector->make(
            Rules::class, 
            [
                ':foundUrlEvent' => $listenEvent, 
                ':skippingLinkEvent' => $skipEvent,
                ':foundUrlToFollowEvent' => $foundEvent
            ]
        );
    }
    
    public function createSiteChecker($foundUrlToFollowEvent, $responseOkEvent)
    {
        return $this->injector->make(
            SiteChecker::class,
            [
                ':foundUrlToFollowEvent' => $foundUrlToFollowEvent,
                ':responseOkEvent' => $responseOkEvent
            ]
        );
    }

    public function createLinkFindingParser($htmlReceivedEvent, $foundUrlEvent)
    {
        return $this->injector->make(
            LinkFindingParser::class,
            [
                ':htmlReceivedEvent' => $htmlReceivedEvent,
                ':foundUrlEvent' => $foundUrlEvent
            ]
        );
    }
    
    public function createSkippingLinkWatcher($skippingLinkEvent) 
    {
        return $this->injector->make(
            SkippingLinkWatcher::class, [
                ':skippingLinkEvent' => $skippingLinkEvent
            ]
        );
    }
    
    public function createContentTypeEventList($responseOkEvent, $htmlReceivedEvent)
    {
        return $this->injector->make(
            ContentTypeEventList::class,
            [
                ':responseOkEvent' => $responseOkEvent, 
                ':htmlReceivedEvent' => $htmlReceivedEvent
            ]
        );
    }
    
    
    public function createContentTypeEventList($responseOkEvent, $htmlReceivedEvent)
    {
        return $this->injector->make(
            ContentTypeEventList::class,
            [
                ':responseOkEvent' => $responseOkEvent, 
                ':htmlReceivedEvent' => $htmlReceivedEvent
            ]
        );
    }
    
    
}
