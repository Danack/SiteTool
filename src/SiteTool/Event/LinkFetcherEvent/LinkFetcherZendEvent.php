<?php


namespace SiteTool\Event\LinkFetcherEvent;

use SiteTool\Event\LinkFetcherEvent;
use SiteTool\Event\RulesEvent;
use SiteTool\Processor\Rules;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use SiteTool\Event\ResponseOkEvent;
use SiteTool\Processor\ResponseValid;
use Amp\Artax\Response;
use SiteTool\Processor\LinkFetcher;
use SiteTool\SiteChecker;


class LinkFetcherZendEvent implements LinkFetcherEvent
{
    /** @var EventManager  */
    private $eventManager;
    
    /** @var LinkFetcher */
    private $linkFetcher;
    
    public function __construct(
        EventManager $eventManager,
        LinkFetcher $linkFetcher
    ) {
        $this->eventManager = $eventManager;
        $this->linkFetcher = $linkFetcher;
        $eventManager->attach(SiteChecker::FOUND_URL_TO_FOLLOW, [$this, 'followURLEvent']);
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
        
        $this->eventManager->trigger(SiteChecker::RESPONSE_RECEIVED, null, $params);
    }
}
