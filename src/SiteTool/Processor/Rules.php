<?php


namespace SiteTool\Processor;

//use SiteTool\Writer\StatusWriter;
//use SiteTool\Writer\ErrorWriter;
use SiteTool\CrawlerConfig;
use SiteTool\UrlToCheck;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\OutputWriter;
use SiteTool\Event\RulesEvent;
use SiteTool\Event\Data\FoundUrlEventData;

use Amp\Artax\Uri;

class Rules
{
    /** @var CrawlerConfig  */
    private $crawlerConfig;

    public function __construct(
        CrawlerConfig $crawlerConfig,
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $this->crawlerConfig = $crawlerConfig;
        $this->outputWriter = $outputWriter;
    }

    /**
     * @param FoundUrlEventData $foundUrlEventData
     * @param RulesEvent $rulesEvent
     * @return null|void
     */
    public function getUrlToCheck(FoundUrlEventData $foundUrlEventData, RulesEvent $rulesEvent)
    {
        $href = $foundUrlEventData->href;
        $referrer = $foundUrlEventData->urlToCheck->getReferrer();
        
        $knownNonLinks = [
            'mailto',       // Mail links
            'javascript',   // Javascript
            '#',            // Anchor tags
            'tel',          // telephone number
            'fax',
            'skype',
            'sms:',
        ];
        
        foreach ($knownNonLinks as $knownNonLink) {
            if (stripos($href, $knownNonLink) === 0) {
                $this->outputWriter->write(
                    OutputWriter::PROGRESS,
                    "skipping known non-link $knownNonLink"
                );
                return null;
            }
        }
        $uri = new Uri($foundUrlEventData->urlToCheck->getUrl());
        $newUri = $uri->resolve($href);
        
        //echo "testing" . $newUri . " " . $uri ." d sdd " . var_export($foundUrlEventData->urlToCheck, true) ."\n";
        
        if (strcasecmp($newUri->getHost(), $this->crawlerConfig->domainName) !== 0) {
            $rulesEvent->skippingLink($href, $newUri->getHost());
            return;
        }
        
        $urlToCheck = new UrlToCheck($newUri->__toString(), $foundUrlEventData->urlToCheck->getUrl());
        //$this->eventManager->trigger($this->foundUrlToFollowEvent, null, [$urlToCheck]);
        $rulesEvent->foundUrlToFollow($urlToCheck);

//        if (strpos($href, '//') === 0) {
//            $href = sprintf("%s://%s",
//                $this->crawlerConfig->schema, // todo, should be schema of referer
//                substr($href, 2)
//            );
//        }

        
        
        
//        $parsedUrl = parse_url($href);
//
//        if (array_key_exists('host', $parsedUrl) === true) {
//            // If it points to a different domain, don't follow.
//            if (endsWith($parsedUrl['host'], $this->crawlerConfig->domainName) === false) {
//                //$this->statusWriter->write("Skipping $href as host " . $parsedUrl['host'] . " is different.");
//                
//                
////                $this->eventManager->trigger(
////                    $this->skippingLinkEvent,
////                    null,
////                    [$href, $parsedUrl['host']]
////                );
//                $rulesEvent->skippingLink($href, $parsedUrl['host']);
//
////                if (strpos($parsedUrl['host'], $this->crawlerConfig->domainName) !== false) {
////                    $this->outputWriter->write(
////                        OutputWriter::ERROR,
////                        "*** PROBABLY BORKED " . $parsedUrl['host'] . ""
////                    );
////                    $this->outputWriter->write(
////                        OutputWriter::ERROR,
////                        "Host probably borked: " . $parsedUrl['host'],
////                        "href $href",
////                        "Referrer $referrer"
////                    );
////                }
//
//                return;
//            }
//
//            // $this->statusWriter->write("Following absolute URL $href");
//            // If it points to same domain, follow.
//            $urlToCheck = new UrlToCheck($href, $referrer);
//            //$this->eventManager->trigger($this->foundUrlToFollowEvent, null, [$urlToCheck]);
//            
//            $rulesEvent->foundUrlToFollow($urlToCheck);
//        }
//
//        //It's relative
//        $urlToCheck = new UrlToCheck(
//            $this->crawlerConfig->getPath($href),
//            $referrer
//        );
//        
//        //$this->eventManager->trigger($this->foundUrlToFollowEvent, null, [$urlToCheck]);
//        
//        $rulesEvent->foundUrlToFollow($urlToCheck);
        
    }
}

