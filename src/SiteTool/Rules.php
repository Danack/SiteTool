<?php


namespace SiteTool;

use SiteTool\Writer\StatusWriter;
use SiteTool\Writer\ErrorWriter;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\OutputWriter;

class Rules
{
    /** @var CrawlerConfig  */
    private $crawlerConfig;

    /** @var StatusWriter  */
    private $statusWriter;

    private $skippingLinkEvent;
    
    private $foundUrlToFollowEvent;
    
    public function __construct(
        CrawlerConfig $crawlerConfig,
        EventManager $eventManager,
        OutputWriter $outputWriter,
//        StatusWriter $statusWriter,
//        ErrorWriter $errorWriter,
        $foundUrlEvent, $skippingLinkEvent, $foundUrlToFollowEvent
    ) {
        $this->crawlerConfig = $crawlerConfig;
//        $this->statusWriter = $statusWriter;
//        $this->errorWriter = $errorWriter;
        $this->outputWriter = $outputWriter;
        $this->eventManager = $eventManager;
        $this->eventManager->attach($foundUrlEvent, [$this, 'foundUrlEvent']);
        
        $this->skippingLinkEvent = $skippingLinkEvent;
        $this->foundUrlToFollowEvent = $foundUrlToFollowEvent;
    }

    public function foundUrlEvent(Event $event)
    {
        $params = $event->getParams();
        $href = $params[0];
        $referrer = $params[1];
        $this->getUrlToCheck($href, $referrer);
    }

    /**
     * @param $href
     * @param $referrer
     * @return null|UrlToCheck
     */
    public function getUrlToCheck($href, $referrer)
    {
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

        if (strpos($href, '//') === 0) {
            $href = sprintf("%s://%s",
                $this->crawlerConfig->schema, // todo, should be schema of referer
                substr($href, 2)
            );
        }

        $parsedUrl = parse_url($href);

        if (array_key_exists('host', $parsedUrl) === true) {
            // If it points to a different domain, don't follow.
            if (endsWith($parsedUrl['host'], $this->crawlerConfig->domainName) === false) {
                //$this->statusWriter->write("Skipping $href as host " . $parsedUrl['host'] . " is different.");
                $this->eventManager->trigger(
                    $this->skippingLinkEvent,
                    null,
                    [$href, $parsedUrl['host']]
                );
                if (strpos($parsedUrl['host'], $this->crawlerConfig->domainName) !== false) {
                    $this->outputWriter->write(
                        OutputWriter::ERROR,
                        "*** PROBABLY BORKED " . $parsedUrl['host'] . ""
                    );
                    $this->outputWriter->write(
                        OutputWriter::ERROR,
                        "Host probably borked: " . $parsedUrl['host'],
                        "href $href",
                        "Referrer $referrer"
                    );
                }

                return;
            }

            // $this->statusWriter->write("Following absolute URL $href");
            // If it points to same domain, follow.
            $urlToCheck = new UrlToCheck($href, $referrer);
            $this->eventManager->trigger($this->foundUrlToFollowEvent, null, [$urlToCheck]);
        }

        //It's relative
        $urlToCheck = new UrlToCheck(
            $this->crawlerConfig->getPath($href),
            $referrer
        );
        
        $this->eventManager->trigger($this->foundUrlToFollowEvent, null, [$urlToCheck]);
    }

    public function shouldFollow($fullURL)
    {
        if (strpos($fullURL, '/queueinfo') !== false) {
            return false;
        }

        return true;
    }
}

