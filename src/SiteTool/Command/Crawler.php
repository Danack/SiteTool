<?php

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\CrawlerConfig;
use SiteTool\Processor\LinkFindingParser;
use SiteTool\Processor\Rules;
use SiteTool\SiteChecker;
use SiteTool\Processor\SkippingLinkWatcher;
use SiteTool\URLToCheck;
use Zend\EventManager\EventManager;
use SiteTool\Processor\ContentTypeEventList;
use SiteTool\PluginFactory;
use SiteTool\Writer\OutputWriter;

use SiteTool\Event\ContentTypeEvent\ContentTypeZendEvent;
use SiteTool\Event\LinkFetcherEvent\LinkFetcherZendEvent;
use SiteTool\Event\MigrateCheckOkStatusEvent\MigrateCheckOkStatusZendEvent;
use SiteTool\Event\ParseEvent\ParseZendEvent;
use SiteTool\Event\ResponseOkEvent\ResponseOkZendEvent;
use SiteTool\Event\RulesEvent\RulesZendEvent;
use SiteTool\Event\SiteCheckOkStatusEvent\SiteCheckOkStatusZendEvent;
use SiteTool\Event\SkippingLinkWatcherEvent\SkippingLinkWatcherZendEvent;

class Crawler
{
    public function run(
        //PluginFactory $pluginFactory,
        CrawlerConfig $crawlerConfig,
        EventManager $eventManager,
        OutputWriter $outputWriter,
        Injector $injector
    ) {
        // This is fine.
        libxml_use_internal_errors(true);

        $relaysToCreate = [
            LinkFetcherZendEvent::class,
            SiteCheckOkStatusZendEvent::class,
            ResponseOkZendEvent::class,
            ContentTypeZendEvent::class,
            ParseZendEvent::class,
            RulesZendEvent::class,
            SkippingLinkWatcherZendEvent::class
        ];

        $eventNames = [
            'foundUrlToFollowEvent' => SiteChecker::FOUND_URL_TO_FOLLOW,
            'responseOkEvent' => SiteChecker::RESPONSE_OK,
            'htmlReceivedEvent' => SiteChecker::HTML_RECEIVED,
            'foundUrlEvent' => SiteChecker::FOUND_URL,
            'skippingLinkEvent' => SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN,
        ];
        
        foreach ($eventNames as $paramName => $value) {
            $injector->defineParam($paramName, $value);
        }

        foreach ($relaysToCreate as $relayToCreate) {
            $relays[] = $injector->make($relayToCreate);
        }

//        $plugins = [
//            //$pluginFactory->createRules(SiteChecker::FOUND_URL, SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN, SiteChecker::FOUND_URL_TO_FOLLOW),
//            $pluginFactory->createSiteChecker( SiteChecker::FOUND_URL_TO_FOLLOW, SiteChecker::RESPONSE_OK),
//            $pluginFactory->createLinkFindingParser(SiteChecker::HTML_RECEIVED , SiteChecker::FOUND_URL),
//            $pluginFactory->createSkippingLinkWatcher(SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN),
//            $pluginFactory->createContentTypeEventList(SiteChecker::RESPONSE_OK, SiteChecker::HTML_RECEIVED),
//        ];
                        

        $firstUrlToCheck = new URLToCheck('http://' . $crawlerConfig->domainName . $crawlerConfig->path, '/');
        $eventManager->trigger(SiteChecker::FOUND_URL_TO_FOLLOW, null, [$firstUrlToCheck]);

        $outputWriter->write(OutputWriter::PROGRESS, "Start.");

        \Amp\run(function() {});
        $outputWriter->write(OutputWriter::PROGRESS, "fin.");
    }
}
