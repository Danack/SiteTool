<?php

namespace SiteTool\EventProcessor;

use SiteTool\Event\ResponseIsValid;
use SiteTool\UrlToCheck;
use SiteTool\Event\FoundUrl;
use SiteTool\Event\FoundUrlToFetch;
use SiteTool\EventManager;
use SiteTool\Event\HtmlToParse;
use Amp\Artax\SocketException;
use FluentDOM\Document;
use FluentDOM\Element;
use SiteTool\Writer\OutputWriter;
use SiteTool\Event\HtmlIsValid;
use SiteTool\Event\EndOfProcessing;

class ParseResponseToGenerateNextCall implements Relay
{

    /** @var callable */
    private $foundUrlEventTrigger;

    private $switchName = "Parse the api response to find links";

    private $itemsFound = 0;


    public function __construct(
        EventManager $eventManager
    ) {
        $eventManager->attachEvent(ResponseIsValid::class, [$this, 'parseResponse'], $this->switchName);
        $eventManager->attachEvent(EndOfProcessing::class, [$this, 'endOfProcessing'], $this->switchName);
        $this->foundUrlEventTrigger = $eventManager->createTrigger(FoundUrlToFetch::class, $this->switchName);
    }

    public function parseResponse(ResponseIsValid $responseIsValid)
    {
        $lowestId = null;
        foreach ($responseIsValid->getData() as $entry) {
            $nextId = $entry["id"];
            $nextId = intval($nextId);
            if ($nextId !== 0) {
                if ($lowestId === null) {
                    $lowestId = $nextId;
                }
                else if ($lowestId > $nextId) {
                    $lowestId = $nextId;
                }
            }

            $this->itemsFound++;
        }

        if ($lowestId === null) {
            echo "lowestId is null, so end of list";
            return;
        }

        $nextPage = "http://api.local.aitekzfinance.com:8000/articles?limit=50&after=" . $lowestId;

        echo "Next page at: " . $nextPage . "\n";

        $urlToCheck = new UrlToCheck($nextPage, null);
        $foundUrlToFetch = new FoundUrlToFetch($urlToCheck);

        ($this->foundUrlEventTrigger)($foundUrlToFetch);
    }

    public function getAsyncWorkers()
    {
        return [];
    }

    public function endOfProcessing()
    {
        printf(
            "We scanned %s items",
            $this->itemsFound
        );
    }
}
