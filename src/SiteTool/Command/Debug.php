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
use SiteTool\Event\FoundUrlToFollow;
use Zend\EventManager\EventManager;


class Debug
{
    private $relays = [];
    
    public function __construct(Injector $injector)
    {
        $processorsToCreate = [
            \SiteTool\EventProcessor\ValidateHtmlToParse::class,
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
        $foundUrlToFollow = new FoundUrlToFollow($firstUrlToCheck);
        $eventManager->trigger(FoundUrlToFollow::class, null, [$foundUrlToFollow]);
        $outputWriter->write(OutputWriter::PROGRESS, "Start.");

        \Amp\run(function() {});
        $outputWriter->write(OutputWriter::PROGRESS, "fin.");
    }
}
