<?php

namespace SiteTool;

use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\Response;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\OutputWriter;

class SiteChecker
{
    const HTTP_RESPONSE     = 'http_response';
    
    const RESPONSE_OK       = 'response_ok';
    const HTML_RECEIVED     = 'html_received';
    const RESPONSE_RECEIVED = 'response_received';
    const FOUND_HREF        = 'found_href';

    const FOUND_URL           = 'found_url';
    const FOUND_URL_TO_FOLLOW = 'found_url_to_scan';
    const REQUEST_ERROR       = 'request_error';
    const PARSING_ERROR       = 'parsing_error';

    const SKIPPING_LINK_DUE_TO_DOMAIN = 'skipping_link_due_to_domain';
//    
//    /** @var URLToCheck[] */
//    private $urlsToCheck = [];
//
//    private $errors = 0;
//    
//    private $count = 0;
//
//    /**
//     * @var ArtaxClient
//     */
//    private $artaxClient;
//    
//    /** @var OutputWriter */
//    private $outputWriter;
//
//    /** @var EventManager */
//    private $eventManager;
//    
//    private $maxCount;
//    
//    private $responseOkEvent;
//    
//    function __construct(
//        ArtaxClient $artaxClient,
//        OutputWriter $outputWriter,
//        EventManager $eventManager,
//        $maxCount,
//        $foundUrlToFollowEvent,
//        $responseOkEvent
//    ) {
//        $this->artaxClient = $artaxClient;
//        $this->outputWriter = $outputWriter;
//        $this->eventManager = $eventManager;
//        $this->maxCount = $maxCount;
//
//        // This is fine.
//        libxml_use_internal_errors(true);
//
//        $eventManager->attach($foundUrlToFollowEvent, [$this, 'followURLEvent']);
//        $this->responseOkEvent = $responseOkEvent;
//    }
}