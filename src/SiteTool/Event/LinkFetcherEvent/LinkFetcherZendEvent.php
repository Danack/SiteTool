<?php


namespace SiteTool\Event\LinkFetcherEvent;

use SiteTool\Event\LinkFetcherEvent;
use SiteTool\Event\RulesEvent;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use SiteTool\Event\ResponseOkEvent;
use Amp\Artax\Response;
use SiteTool\Processor\LinkFetcher;
use SiteTool\SiteChecker;
use SiteTool\EventManager;


class LinkFetcherZendEvent implements LinkFetcherEvent
{
    /** @var LinkFetcher */
    private $linkFetcher;
    
    /** @var  callable */
    private $responseReceivedTrigger;
    
    private $switchName = "Fetch the URL";
    
    public function __construct(
        EventManager $eventManager,
        LinkFetcher $linkFetcher,
        $foundUrlToFollowEvent
    ) {
        $this->linkFetcher = $linkFetcher;
        $this->responseReceivedTrigger = $eventManager->createTrigger(SiteChecker::RESPONSE_RECEIVED, $this->switchName);
        $eventManager->attach(SiteChecker::FOUND_URL_TO_FOLLOW, [$this, 'followURLEvent'], $this->switchName);
    }

    /**
     * @param Event $e
     */
    public function followURLEvent(Event $e)
    {
        $params = $e->getParams();
        $this->linkFetcher->followURL($params[0], $this);
    }

    function resultFetched(
        \Exception $e = null,
        Response $response = null,
        UrlToCheck $urlToCheck,
        $fullURL
    ) {
        $params = [
            $e,
            $response, 
            $urlToCheck,
            $fullURL
        ];

        $fn = $this->responseReceivedTrigger;
        $fn($params);
    }
}
