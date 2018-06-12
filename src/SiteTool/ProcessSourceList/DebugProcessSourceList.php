<?php

declare(strict_types=1);

namespace SiteTool\ProcessSourceList;

use SiteTool\ProcessSourceList;
use Zend\EventManager\EventManager;
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
            \SiteTool\EventProcessor\ValidateHtmlToParse::class,
        ];

        return $processorsToCreate;
    }

    public function getSetupFunction()
    {
        return \Closure::fromCallable([$this, 'setup']);
    }

    private function setup()
    {
//        $firstUrlToCheck = new UrlToCheck('http://phpimagick.com/', 'http://phpimagick.com/');
//        $foundUrlToFollow = new FoundUrlToFetch($firstUrlToCheck);
//        $this->eventManager->trigger(FoundUrlToFetch::class, null, [$foundUrlToFollow]);
//
        //TODO - trigger an appropriate event.
    }

    public function endOfProcessing()
    {
        $this->eventManager->trigger(EndOfProcessing::class, null, []);
    }
}

