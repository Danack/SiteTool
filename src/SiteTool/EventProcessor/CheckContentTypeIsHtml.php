<?php

namespace SiteTool\EventProcessor;

use SiteTool\EventManager;
use SiteTool\Event\HtmlToParse;
use SiteTool\Event\ResponseOk;

class CheckContentTypeIsHtml implements Relay
{
    /** @var callable  */
    private $htmlReceivedTrigger;

    private $switchName = "Is the response HTML?";
    
    public function __construct(EventManager $eventManager)
    {
        $eventManager->attachEvent(ResponseOk::class, [$this, 'checkResponseType'], $this->switchName);
        $this->htmlReceivedTrigger = $eventManager->createTrigger(HtmlToParse::class, $this->switchName);
    }

    /**
     * @param ResponseOk $checkResponseType
     * @throws \Exception
     */
    public function checkResponseType(ResponseOk $checkResponseType)
    {
        $response = $checkResponseType->responseReceived;
        $urlToCheck = $checkResponseType->urlToCheck;
        $contentTypeHeader = $response->getResponse()->getHeader('Content-Type');

        if ($contentTypeHeader === null) {
            throw new \Exception("Content-type header not set.");
        }

        $contentType = $contentTypeHeader;
        $colonPosition = strpos($contentType, ';');

        if ($colonPosition !== false) {
            $contentType = substr($contentType, 0, $colonPosition);
        }

        if ($contentType === 'text/html') {
            $fn = $this->htmlReceivedTrigger;
            $fn(new HtmlToParse($urlToCheck, $response->getResponse(), $response->getResponseBody()));
            return;
        }
    }

    public function getAsyncWorkers()
    {
        return [];
    }
}
