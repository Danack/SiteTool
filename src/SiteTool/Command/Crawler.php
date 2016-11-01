<?php

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\CrawlerConfig;
use SiteTool\Processor\LinkFindingParser;
use SiteTool\Rules;
use SiteTool\SiteChecker;
use SiteTool\Processor\SkippingLinkWatcher;
use SiteTool\URLToCheck;
use SiteTool\Writer\StatusWriter;
use Zend\EventManager\EventManager;
use SiteTool\Processor\ContentTypeEventList;

class Crawler
{
    public function run(
        Injector $injector,
        CrawlerConfig $crawlerConfig,
        EventManager $eventManager,
        StatusWriter $statusWriter
    ) {
        $plugins = [
            Rules::class,
            SiteChecker::class,
            LinkFindingParser::class,
            SkippingLinkWatcher::class,
            ContentTypeEventList::class,
        ];

        $pluginInstances = [];
        foreach ($plugins as $plugin) {
            $pluginInstances[] = $injector->make($plugin);
        }

        $firstUrlToCheck = new URLToCheck('http://' . $crawlerConfig->domainName . $crawlerConfig->path, '/');
        $eventManager->trigger(SiteChecker::FOUND_URL_TO_FOLLOW, null, [$firstUrlToCheck]);

        $statusWriter->write("Start.");

        \Amp\run(function() {});
        $statusWriter->write("fin.");
    }
}
