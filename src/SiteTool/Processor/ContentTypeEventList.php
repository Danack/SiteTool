<?php


namespace SiteTool\Processor;

use Amp\Artax\Response;
use SiteTool\SiteChecker;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

class ContentTypeEventList
{
    /** @var EventManager */
    private $eventManager;
    
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        $eventManager->attach(SiteChecker::RESPONSE_OK, [$this, 'responseOkEvent']);
    }

    function responseOkEvent(Event $event)
    {
        $params = $event->getParams();
        $response = $params[0];
        $urlToCheck = $params[1];

        $this->triggerEventForContent($response, $urlToCheck);
    }
    
    /**
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @return null
     * @throws \Exception
     */
    public function triggerEventForContent(Response $response, UrlToCheck $urlToCheck)
    {
        $contentTypeHeaders = $response->getHeader('Content-Type');

        if (array_key_exists(0, $contentTypeHeaders) == false) {
            throw new \Exception("Content-type header not set.");
        }

        $contentType = $contentTypeHeaders[0];
        $colonPosition = strpos($contentType, ';');

        if ($colonPosition !== false) {
            $contentType = substr($contentType, 0, $colonPosition);
        }
        
        $contentTypeEvents = [
            'text/html' => SiteChecker::HTML_RECEIVED,
        ];

//        $ignoredContentTypes = [
//            'text/plain',
//            'application/octet-stream',
//            'image/gif',
//            'image/jpeg',
//            'image/jpg',
//            'image/vnd.adobe.photoshop',
//            'image/png',
//            'application/atom+xml',
//        ];
        
        if (array_key_exists($contentType, $contentTypeEvents) === true) {
            $event = $contentTypeEvents[$contentType];
            $this->eventManager->trigger($event, null, [$urlToCheck, $response->getBody()]);
            return;
        }

        // throw new \Exception("Unrecognised content-type $contentType");
    }
}
