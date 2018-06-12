<?php

declare(strict_types=1);

namespace SiteTool\ProcessSourceList;

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

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function getProcessList()
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

        return $processorsToCreate;
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

