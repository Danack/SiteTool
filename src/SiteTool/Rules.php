<?php


namespace SiteTool;

use SiteTool\StatusWriter;
use SiteTool\ErrorWriter;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;

class Rules
{
    /** @var CrawlerConfig  */
    private $crawlerConfig;

    /** @var StatusWriter  */
    private $statusWriter;

    public function __construct(
        CrawlerConfig $crawlerConfig,
        EventManager $eventManager,
        StatusWriter $statusWriter,
        ErrorWriter $errorWriter
    ) {
        $this->crawlerConfig = $crawlerConfig;
        $this->statusWriter = $statusWriter;
        $this->errorWriter = $errorWriter;
        $this->eventManager = $eventManager;
        $this->eventManager->attach(SiteChecker::FOUND_URL, [$this, 'foundUrlEvent']);
    }

    public function foundUrlEvent(Event $event)
    {
        $params = $event->getParams();
        $href = $params[0];
        $referrer = $params[1];
        //echo "Received FOUND_URL with $href, $referrer \n";
        
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
                $this->statusWriter->write("skipping known non-link $knownNonLink");
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
                    SiteChecker::SKIPPING_LINK_DUE_TO_DOMAIN,
                    null,
                    [$href, $parsedUrl['host']]
                );
                if (strpos($parsedUrl['host'], $this->crawlerConfig->domainName) !== false) {
                    $this->statusWriter->write("*** PROBABLY BORKED " . $parsedUrl['host'] . "");
                    $this->errorWriter->write(
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
            //echo "FOUND_URL_TO_FOLLOW, $href \n";
            $this->eventManager->trigger(SiteChecker::FOUND_URL_TO_FOLLOW, null, [$urlToCheck]);
        }

        //It's relative
        $urlToCheck = new UrlToCheck(
            $this->crawlerConfig->getPath($href),
            $referrer
        );
        
        //echo "FOUND_URL_TO_FOLLOW, $href \n";
        $this->eventManager->trigger(SiteChecker::FOUND_URL_TO_FOLLOW, null, [$urlToCheck]);
    }

    public function shouldFollow($fullURL)
    {
        if (strpos($fullURL, '/queueinfo') !== false) {
            return false;
        }

        return true;
    }
}

