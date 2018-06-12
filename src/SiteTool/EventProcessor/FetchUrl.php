<?php

namespace SiteTool\EventProcessor;

use SiteTool\UrlToCheck;
use SiteTool\EventManager;
use SiteTool\Event\ResponseReceived;
use SiteTool\Event\FoundUrlToFetch;
use SiteTool\Event\ResponseError;
use Amp\Artax\Client as ArtaxClient;
use SiteTool\Writer\OutputWriter;

class FetchUrl implements Relay
{
    /** @var  callable */
    private $responseReceivedTrigger;

    /** @var  callable */
    private $responseErrorTrigger;
    
    private $switchName = "Fetch the URL";
    
    /** @var URLToCheck[] */
    private $urlsToCheck = [];
    
    private $count = 0;

    /**
     * @var ArtaxClient
     */
    private $artaxClient;
    
    /** @var OutputWriter */
    private $outputWriter;

    private $maxCount;

    /** @var \Amp\Deferred */
    private $waiting;

    /** @var \SplQueue */
    private $queue;


    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter,
        ArtaxClient $artaxClient
    ) {
        $this->outputWriter = $outputWriter;
        $this->maxCount = 10000;
        $this->artaxClient = $artaxClient;
        $eventManager->attachEvent(FoundUrlToFetch::class, [$this, 'followURL'], $this->switchName);
        $this->responseReceivedTrigger = $eventManager->createTrigger(ResponseReceived::class, $this->switchName);
        $this->responseErrorTrigger = $eventManager->createTrigger(ResponseError::class, $this->switchName);

        $this->queue = new \SplQueue;
        $this->waiting = new \Amp\Deferred;
    }


    public function doWork()
    {
        while (true) {
            while ($this->queue->isEmpty()) {
                yield $this->waiting->promise();
            }
            $urlToCheck = $this->queue->pop();
            /** @var \SiteTool\UrlToCheck $urlToCheck */
            $response = yield $this->artaxClient->request($urlToCheck->getUrl());
            $responseBody = yield $response->getBody();

            printf(
                "# %s → %s %s (queue size: %d)" . PHP_EOL,
                $urlToCheck->getUrl(),
                $response->getStatus(),
                $response->getReason(),
                $this->queue->count()
            );

            $fn = $this->responseReceivedTrigger;
            $fn(new ResponseReceived(
                $response,
                $responseBody,
                $urlToCheck
            ));

            $deferred = $this->waiting;
            $this->waiting = new \Amp\Deferred;
            $deferred->resolve();
        }
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    public function followURL(FoundUrlToFetch $foundUrlToFollow)
    {
        $urlToCheck = $foundUrlToFollow->urlToCheck;
        if (array_key_exists($urlToCheck->getUrl(), $this->urlsToCheck) === true) {
            // already scanning this URL
            return;
        }

        if ($this->count >= $this->maxCount) {
            // We've hit the limit for how many URLs to scan.
            return;
        }

        $this->count++;

        $this->urlsToCheck[$urlToCheck->getUrl()] = null;
        $this->queue->push($urlToCheck);
    }

    public function getAsyncWorkers()
    {
        return [
            [$this, 'doWork'],
            [$this, 'doWork'],
            [$this, 'doWork'],
            [$this, 'doWork'],
            [$this, 'doWork'],
            [$this, 'doWork'],
            [$this, 'doWork'],
            [$this, 'doWork'],
        ];
    }
}
