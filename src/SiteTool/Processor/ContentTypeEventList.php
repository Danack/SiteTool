<?php


namespace SiteTool\Processor;

use Amp\Artax\Response;
use SiteTool\SiteChecker;
use SiteTool\UrlToCheck;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use SiteTool\Event\ContentTypeEvent;

class ContentTypeEventList
{
    /**
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @return null
     * @throws \Exception
     */
    public function triggerEventForContent(
        Response $response,
        UrlToCheck $urlToCheck,
        ContentTypeEvent $contentTypeEvent)
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
            $contentTypeEvent->htmlReceived($response, $urlToCheck);
            return;
        }
    }
}
