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
use SiteTool\EventProcessor\ValidateApiResponse;
use SiteTool\EventProcessor\ParseResponseToGenerateNextCall;
use SiteTool\Event\EndOfProcessing;


class ApiCheckerProcessSourceList implements ProcessSourceList
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
            //LogResponseIsOk::class,
            //LogSkippedLink::class
            CheckResponseIsOk::class,
            ValidateApiResponse::class,
            // ParseResponseToGenerateNextCall::class
        ];

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

        for ($id = 200; $id > 0; $id -= 50) {
            $uri = 'http://api.local.aitekzfinance.com/articles?limit=50&after=' . $id;
            $firstUrlToCheck = new UrlToCheck($uri, null);
            $foundUrlToFollow = new FoundUrlToFetch($firstUrlToCheck);
            $this->eventManager->trigger(FoundUrlToFetch::class, null, [$foundUrlToFollow]);
        }
    }

    public function endOfProcessing()
    {
        $this->eventManager->trigger(EndOfProcessing::class, null, [null]);
    }
}

