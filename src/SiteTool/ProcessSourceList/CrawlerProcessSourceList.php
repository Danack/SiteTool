<?php

declare(strict_types=1);

namespace SiteTool\ProcessSourceList;

use Auryn\Injector;
use SiteTool\EventProcessor\CheckContentTypeIsHtml;
use SiteTool\EventProcessor\FetchUrl;
use SiteTool\EventProcessor\ParseHtmlToFindLinks;
use SiteTool\EventProcessor\CheckResponseIsOk;
use SiteTool\EventProcessor\ShouldUrlFoundBeFollowed;
use SiteTool\EventProcessor\LogResponseIsOk;
use SiteTool\EventProcessor\LogSkippedLink;
use SiteTool\ProcessSourceList;
use SiteTool\UrlToCheck;
use Zend\EventManager\EventManager;
use SiteTool\Event\FoundUrlToFetch;
use SiteTool\Event\EndOfProcessing;

class CrawlerProcessSourceList implements ProcessSourceList
{
    /** @var EventManager */
    private $eventManager;

    /** @var Injector */
    private $injector;

    public function __construct(EventManager $eventManager, Injector $injector)
    {
        $this->eventManager = $eventManager;
        $this->injector = $injector;
    }

    public function getEventProcessors()
    {
        $processorsToCreate = [
            FetchUrl::class,
            LogResponseIsOk::class,
            CheckResponseIsOk::class,
            CheckContentTypeIsHtml::class,
            ParseHtmlToFindLinks::class,
            ShouldUrlFoundBeFollowed::class,
            LogSkippedLink::class
        ];

        $relays = [];
        foreach ($processorsToCreate as $processorToCreate) {
            $relays[] = $this->injector->make($processorToCreate);
        }

        return $relays;
    }

    public function getSetupFunction()
    {
        return \Closure::fromCallable([$this, 'setup']);
    }

    private function setup()
    {
        $firstUrlToCheck = new UrlToCheck('http://phpimagick.com/', 'http://phpimagick.com/');
        $foundUrlToFollow = new FoundUrlToFetch($firstUrlToCheck);
        $this->eventManager->trigger(FoundUrlToFetch::class, null, [$foundUrlToFollow]);
    }

    public function endOfProcessing()
    {
        $this->eventManager->trigger(EndOfProcessing::class, null, []);
    }
}
