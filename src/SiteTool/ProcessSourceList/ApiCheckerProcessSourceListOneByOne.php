<?php

declare(strict_types=1);

namespace SiteTool\ProcessSourceList;

use Auryn\Injector;
use SiteTool\EventProcessor\FetchUrl;
use SiteTool\EventProcessor\CheckResponseIsOk;
use SiteTool\ProcessSourceList;
use SiteTool\UrlToCheck;
use Zend\EventManager\EventManager;
use SiteTool\Event\FoundUrlToFetch;
use SiteTool\EventProcessor\ValidateApiResponse;
use SiteTool\Event\EndOfProcessing;

class ApiCheckerProcessSourceListOneByOne implements ProcessSourceList
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

    /**
     * @return \SiteTool\EventProcessor\Relay[]
     */
    public function getEventProcessors()
    {
        $processorsToCreate = [
            FetchUrl::class,
            CheckResponseIsOk::class,
            ValidateApiResponse::class,
        ];

        $relays = [];
        foreach ($processorsToCreate as $processorToCreate) {
            $relays[] = $this->injector->make($processorToCreate);
        }

        return $processorsToCreate;
    }

    public function getSetupFunction()
    {
        return \Closure::fromCallable([$this, 'setup']);
    }

    private function setup()
    {
        $startUrl = 'http://api.local.aitekzfinance.com/articles?limit=50';

        $json = file_get_contents($startUrl);
        $data = json_decode($json, true);

        $maxId = null;

        foreach ($data as $entry) {
            $maxId = $entry['id'];
            break;
        }

        if ($maxId === null) {
            echo "Failed to get max Id from ";
            exit(-1);
        }

        $firstUrlToCheck = new UrlToCheck('http://api.local.aitekzfinance.com/articles?limit=50', null);
        $foundUrlToFollow = new FoundUrlToFetch($firstUrlToCheck);
        $this->eventManager->trigger(FoundUrlToFetch::class, null, [$foundUrlToFollow]);
    }


    public function endOfProcessing()
    {
        $this->eventManager->trigger(EndOfProcessing::class, null, []);
    }
}
