<?php

namespace SiteTool\Event\ContentTypeEvent;

use SiteTool\Event\ContentTypeEvent;
use Zend\EventManager\Event;
//use Zend\EventManager\EventManager;
use SiteTool\SiteChecker;
use SiteTool\UrlToCheck;
use Amp\Artax\Response;
use SiteTool\Processor\ContentTypeEventList;
use SiteTool\EventManager;

class ContentTypeZendEvent implements ContentTypeEvent 
{
//    /** @var ContentTypeEventList  */
//    private $contentTypeEventList;
    
//    /** @var EventManager  */
//    private $eventManager;

    /** @var callable  */
    private $htmlReceivedTrigger;

    private $switchName = "Is the response HTML?";
    
    public function __construct(
        EventManager $eventManager//,
        //ContentTypeEventList $contentTypeEventList
    ) {
        // $this->eventManager = $eventManager;
        $eventManager->attach(SiteChecker::RESPONSE_OK, [$this, 'responseOkEvent'], $this->switchName);
        $this->htmlReceivedTrigger = $eventManager->createTrigger(SiteChecker::HTML_RECEIVED, $this->switchName);
        //$this->contentTypeEventList = $contentTypeEventList;
    }
    
//    function htmlReceived(Response $response, UrlToCheck $urlToCheck)
//    {
//        //$this->eventManager->trigger(SiteChecker::HTML_RECEIVED, null, [$urlToCheck, $response->getBody()]);
//        $fn = $this->htmlReceivedTrigger;
//        $fn([$urlToCheck, $response->getBody()]);
//    }

    function responseOkEvent(Event $event)
    {
        $params = $event->getParams();
        $response = $params[0];
        $urlToCheck = $params[1];

        $this->triggerEventForContent($response, $urlToCheck, $this);
    }
    
        /**
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @return null
     * @throws \Exception
     */
    public function triggerEventForContent(
        Response $response,
        UrlToCheck $urlToCheck//,
        // ContentTypeEvent $contentTypeEvent
    ) {
        $contentTypeHeaders = $response->getHeader('Content-Type');

        if (array_key_exists(0, $contentTypeHeaders) == false) {
            throw new \Exception("Content-type header not set.");
        }

        $contentType = $contentTypeHeaders[0];
        $colonPosition = strpos($contentType, ';');

        if ($colonPosition !== false) {
            $contentType = substr($contentType, 0, $colonPosition);
        }

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
        if ($contentType === 'text/html') {
            //$contentTypeEvent->htmlReceived($response, $urlToCheck);
            $fn = $this->htmlReceivedTrigger;
            $fn([$urlToCheck, $response->getBody()]);
            return;
        }
    }
    
    
}

