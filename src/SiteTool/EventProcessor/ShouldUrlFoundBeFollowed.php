<?php


namespace SiteTool\EventProcessor;

use SiteTool\UrlToCheck;
use SiteTool\EventManager;
use SiteTool\Event\FoundUrl;
use SiteTool\CrawlerConfig;
use SiteTool\Writer\OutputWriter;
use Amp\Artax\Uri;
use SiteTool\Event\FoundUrlToFollow;
use SiteTool\Event\FoundUrlToSkip;

class ShouldUrlFoundBeFollowed
{
    /** @var  callable */
    private $skippingLinkTrigger;
    
    /** @var  callable */
    private $foundUrlToFollowTrigger;

    private $switchName = "Should we follow this URL?";
    
    public function __construct(
        EventManager $eventManager,
        CrawlerConfig $crawlerConfig,
        OutputWriter $outputWriter
    ) {
        $this->outputWriter = $outputWriter;
        $this->crawlerConfig = $crawlerConfig;

        $eventManager->attachEvent(FoundUrl::class, [$this, 'getUrlToCheck'], $this->switchName);
        $this->skippingLinkTrigger =  $eventManager->createTrigger(FoundUrlToSkip::class, $this->switchName);
        $this->foundUrlToFollowTrigger =  $eventManager->createTrigger(FoundUrlToFollow::class, $this->switchName);
    }

       /**
     * @param FoundUrl $foundUrlEventData
     * @return null|void
     */
    public function getUrlToCheck(FoundUrl $foundUrlEventData)
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

        if (strcasecmp($newUri->getHost(), $this->crawlerConfig->domainName) !== 0) {
            $this->skippingLink($href, $newUri->getHost());
            return;
        }

        $urlToCheck = new UrlToCheck($newUri->__toString(), $foundUrlEventData->urlToCheck->getUrl());
        $this->foundUrlToFollow($urlToCheck);
    }

    public function skippingLink($href, $host)
    {
        $fn = $this->skippingLinkTrigger;
        $fn(new FoundUrlToSkip($href, $host));
    }

    public function foundUrlToFollow(UrlToCheck $urlToCheck)
    {
        $fn = $this->foundUrlToFollowTrigger;
        $fn(new FoundUrlToFollow($urlToCheck));
    }
}
