<?php

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\CrawlerConfig;
use SiteTool\UrlToCheck;
use SiteTool\Writer\OutputWriter;
use SiteTool\EventProcessor\CheckContentTypeIsHtml;
use SiteTool\EventProcessor\FetchUrl;
use SiteTool\EventProcessor\ParseHtmlToFindLinks;
use SiteTool\EventProcessor\CheckResponseIsOk;
use SiteTool\EventProcessor\ShouldUrlFoundBeFollowed;
use SiteTool\EventProcessor\LogResponseIsOk;
use SiteTool\EventProcessor\LogSkippedLink;
use SiteTool\GraphVizBuilder;
use SiteTool\Event\FoundUrlToFetch;
use Zend\EventManager\EventManager;

class Crawler
{
    private $relays = [];
    
    public function __construct(Injector $injector)
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

        foreach ($processorsToCreate as $relayToCreate) {
            // This just holds a references to the object, to stop
            // it from being GC'd.
            $this->relays[] = $injector->make($relayToCreate);
        }
    }

    public function run(
        EventManager $eventManager,
        CrawlerConfig $crawlerConfig,
        OutputWriter $outputWriter,
        GraphVizBuilder $graphVizBuilder,
        $graph
    ) {
        if ($graph) {
            $graphVizBuilder->finalize();
            return;
        }
        
        $firstUrlToCheck = new UrlToCheck('http://' . $crawlerConfig->domainName . $crawlerConfig->path, '/');
        $foundUrlToFollow = new FoundUrlToFetch($firstUrlToCheck);
        $callables = $eventManager->trigger(FoundUrlToFetch::class, null, [$foundUrlToFollow]);

        foreach ($callables as $callable) {
            \Amp\call($callable);
        }


        $outputWriter->write(OutputWriter::PROGRESS, "Start.");

        \Amp\Loop::run(function () {});
        $outputWriter->write(OutputWriter::PROGRESS, "fin.");
    }
}
