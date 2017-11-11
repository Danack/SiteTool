<?php

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\CrawlerConfig;
use SiteTool\UrlToCheck;
use SiteTool\Writer\OutputWriter;
use SiteTool\Processor\CheckContentTypeIsHtml;
use SiteTool\Processor\FetchUrl;
use SiteTool\Processor\ParseHtmlToFindLinks;
use SiteTool\Processor\CheckResponseIsOk;
use SiteTool\Processor\ShouldUrlFoundBeFollowed;
use SiteTool\Processor\LogResponseIsOk;
use SiteTool\Processor\LogSkippedLink;
use SiteTool\GraphVizBuilder;
use SiteTool\Processor\Data\FoundUrlToFollow;
use Zend\EventManager\EventManager;


class Debug
{
    private $relays = [];
    
    public function __construct(Injector $injector)
    {
        $processorsToCreate = [
            \SiteTool\Processor\ValidateHtmlToParse::class,
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
