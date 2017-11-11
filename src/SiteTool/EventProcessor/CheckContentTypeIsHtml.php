<?php

namespace SiteTool\EventProcessor;

use SiteTool\EventManager;
use SiteTool\Event\CheckResponseType;
use SiteTool\Event\HtmlToParse;
use SiteTool\Event\ResponseOk;


class CheckContentTypeIsHtml 
{
    /** @var callable  */
    private $htmlReceivedTrigger;

    private $switchName = "Is the response HTML?";
    
    public function __construct(EventManager $eventManager)
    {
        $eventManager->attachEvent(ResponseOk::class , [$this, 'checkResponseType'], $this->switchName);
        $this->htmlReceivedTrigger = $eventManager->createTrigger(HtmlToParse::class, $this->switchName);
    }

    /**
     * @param ResponseOk $checkResponseType
     * @throws \Exception
     */
    public function checkResponseType(ResponseOk $checkResponseType) {
        
        $response = $checkResponseType->response;
        $urlToCheck = $checkResponseType->urlToCheck;
        
        $contentTypeHeaders = $response->getHeader('Content-Type');

        if (array_key_exists(0, $contentTypeHeaders) == false) {
            throw new \Exception("Content-type header not set.");
        }

        $contentType = $contentTypeHeaders[0];
        $colonPosition = strpos($contentType, ';');

        if ($colonPosition !== false) {
            $contentType = substr($contentType, 0, $colonPosition);
        }

        if ($contentType === 'text/html') {
            $fn = $this->htmlReceivedTrigger;
            $fn(new HtmlToParse($urlToCheck, $response));
            return;
        }
    }
}

