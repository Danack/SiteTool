<?php

declare(strict_types=1);

namespace SiteTool\Command;

use Amp\Loop;
use Amp\Artax\DefaultClient as ArtaxClient;
use Auryn\Injector;

use SiteTool\EventProcessor\CheckContentTypeIsHtml;
use SiteTool\EventProcessor\FetchUrl;
use SiteTool\EventProcessor\ParseHtmlToFindLinks;
use SiteTool\EventProcessor\CheckResponseIsOk;
use SiteTool\EventProcessor\ShouldUrlFoundBeFollowed;
use SiteTool\EventProcessor\LogResponseIsOk;
use SiteTool\EventProcessor\LogSkippedLink;
use SiteTool\UrlToCheck;
use SiteTool\Event\FoundUrlToFetch;
use Zend\EventManager\EventManager;

class GoGoGo
{
    /** @var \SplQueue */
    private $queue;

    /** @var \Amp\Deferred */
    private $waiting;

    /** @var ArtaxClient */
    private $httpClient;

    /** @var Injector  */
    private $injector;

    /** @var  \SiteTool\EventProcessor\Relay[] */
    private $relays;

    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }

    protected function setup()
    {
        $this->queue = new \SplQueue;
        $this->waiting = new \Amp\Deferred;
        $this->httpClient = new ArtaxClient;

        $processorsToCreate = [
            FetchUrl::class,
            LogResponseIsOk::class,
            CheckResponseIsOk::class,
            CheckContentTypeIsHtml::class,
            ParseHtmlToFindLinks::class,
            ShouldUrlFoundBeFollowed::class,
            LogSkippedLink::class
        ];

        foreach ($processorsToCreate as $relayToCreate) {
            // This just holds a references to the object, to stop
            // it from being GC'd.
            $this->relays[] = $this->injector->make($relayToCreate);
        }
    }

    public function run(EventManager $eventManager)
    {
        $this->setup();
        $coroutines = [];

        foreach ($this->relays as $relay) {
            $workers = $relay->getAsyncWorkers();

            foreach ($workers as $worker) {
                $coroutines[] = \Amp\coroutine($worker);
            }
        }

        Loop::run(function () use ($coroutines, $eventManager) {

            $firstUrlToCheck = new UrlToCheck('http://phpimagick.com/', 'http://phpimagick.com/');
            $foundUrlToFollow = new FoundUrlToFetch($firstUrlToCheck);
            $eventManager->trigger(FoundUrlToFetch::class, null, [$foundUrlToFollow]);

            $runningThings = [];

            foreach ($coroutines as $coroutine) {
                $runningThings[] = $coroutine();
            }

            yield $runningThings;

            // Because we automatically exit the event loop, this line will never be reached.
            // If some end condition is known, the while (true) in the workers can be replaced
            // and then this line will be reached once finished.
            print "Finished processing." . PHP_EOL; // <-- Never reached
        });
    }
}
