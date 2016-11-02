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
use SiteTool\PluginFactory;
use SiteTool\Writer\OutputWriter;

class Crawler
{
    public function run(
        
        PluginFactory $pluginFactory,
        CrawlerConfig $crawlerConfig,
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $plugins = [
            $pluginFactory->createRules(SiteChecker::FOUND_URL, SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN, SiteChecker::FOUND_URL_TO_FOLLOW),
            $pluginFactory->createSiteChecker( SiteChecker::FOUND_URL_TO_FOLLOW, SiteChecker::RESPONSE_OK),
            $pluginFactory->createLinkFindingParser(SiteChecker::HTML_RECEIVED , SiteChecker::FOUND_URL),
            $pluginFactory->createSkippingLinkWatcher(SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN),
            $pluginFactory->createContentTypeEventList(SiteChecker::RESPONSE_OK, SiteChecker::HTML_RECEIVED), 
        ];

        $firstUrlToCheck = new URLToCheck('http://' . $crawlerConfig->domainName . $crawlerConfig->path, '/');
        $eventManager->trigger(SiteChecker::FOUND_URL_TO_FOLLOW, null, [$firstUrlToCheck]);

        $outputWriter->write(OutputWriter::PROGRESS, "Start.");

        \Amp\run(function() {});
        $outputWriter->write(OutputWriter::PROGRESS, "fin.");
    }
}
