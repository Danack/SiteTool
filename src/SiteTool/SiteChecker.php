<?php

namespace SiteTool;

use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\SocketException;
use Amp\Artax\Response;
use FluentDOM\Document;
use FluentDOM\Element;
use SiteTool\ResultWriter\FileResultWriter;
use SiteTool\ErrorWriter;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Rules; 


class SiteChecker
{
    const HTTP_RESPONSE     = 'http_response';
    const HTML_RECEIVED     = 'html_received';
    const RESPONSE_RECEIVED = 'response_received';
    const FOUND_HREF        = 'found_href';

    const FOUND_URL         = 'found_url';
    const FOUND_URL_TO_FOLLOW = 'found_url_to_scan';
    const REQUEST_ERROR     = 'request_error';
    const PARSING_ERROR     = 'parsing_error';
    
    
    const SKIPPING_LINK_DUE_TO_DOMAIN = 'skipping_link_due_to_domain';
    
    /** @var URLToCheck[] */
    private $urlsToCheck = [];
    
    /** @var CrawlerConfig  */
    private $crawlerConfig;
    
    private $errors = 0;
    
    /** @var Rules  */
    private $rules;
    
    private $count = 0;

    /**
     * @var ArtaxClient
     */
    private $artaxClient;

    /** @var  \SiteTool\ResultWriter */
    private $resultWriter;

    /** @var  \SiteTool\StatusWriter */
    private $statusWriter;
    
    /** @var EventManager */
    private $eventManager;
    
    private $maxCount;
    
    function __construct(
        CrawlerConfig $crawlerConfig,
        ArtaxClient $artaxClient,
        ResultWriter $resultWriter,
        StatusWriter $statusWriter,
        ErrorWriter $errorWriter,
        EventManager $eventManager,
        Rules $rules,
        ContentTypeEventList $contentTypeEvent,
        $maxCount
    ) {
        $this->crawlerConfig = $crawlerConfig;
        $this->artaxClient = $artaxClient;
        $this->rules = $rules;
        $this->resultWriter = $resultWriter;
        $this->statusWriter = $statusWriter;
        $this->errorWriter = $errorWriter;
        $this->eventManager = $eventManager;
        $this->maxCount = $maxCount;
        $this->contentTypeEvent = $contentTypeEvent;

        // This is fine.
        libxml_use_internal_errors(true);

        $eventManager->attach(SiteChecker::FOUND_URL_TO_FOLLOW, [$this, 'followURLEvent']);
    }


    /**
     * @param \Exception $e
     * @param Response $response
     * @param $fullURL
     * @return null
     */
    function handleException(\Exception $e, Response $response = null, $fullURL)
    {
        $message = "Something went wrong for $fullURL : " . $e->getMessage();
        if ($response) {
            $message .= "Headers " . var_export($response->getAllHeaders(), true);
        }
        $this->statusWriter->write($message);
        $this->errors++;

        return null;
    }

    /**
     * @param \Exception $e
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @param $fullURL
     * @return null|void
     * @throws \Exception
     */
    function analyzeResult(\Exception $e = null, Response $response = null, UrlToCheck $urlToCheck, $fullURL)
    {
        static $first = true;
        
        if ($e) {
            $this->handleException($e, $response, $fullURL);
            return;
        }
    
        $status = $response->getStatus();

        $this->resultWriter->write(
            $urlToCheck->getUrl(),
            $status,
            $urlToCheck->getReferrer()
        );

        if ($status != 200 && $status != 420 && $status != 202) {
            if ($first == true) {
                $first = false;
            }
            else {
                $this->statusWriter->write("Status $status is not OK for " . $urlToCheck->getUrl());
                $this->errors++;
                return null;
            }
        }

        $this->contentTypeEvent->triggerEventForContent($response, $urlToCheck);
    }

    function getErrorCount()
    {
        return $this->errors;
    }

    /**
     * @param Event $e
     */
    public function followURLEvent(Event $e)
    {
        $params = $e->getParams();
        $this->followURL($params[0]);
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    function fetchURL(URLToCheck $urlToCheck)
    {
        $fullURL = $urlToCheck->getUrl();
        $this->statusWriter->write("Fetching $fullURL from referrer " . $urlToCheck->getReferrer());
        $promise = $this->artaxClient->request($fullURL);

        $analyzeResult = function(
            \Exception $e = null,
            Response $response = null
        ) use ($urlToCheck, $fullURL) {
            $this->statusWriter->write("Got $fullURL");
            return $this->analyzeResult($e, $response, $urlToCheck, $fullURL);
        };

        $promise->when($analyzeResult);

        return $promise;
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    public function followURL(URLToCheck $urlToCheck)
    {
        
        $url = $urlToCheck->getUrl();
        if (array_key_exists($url, $this->urlsToCheck) === true) {
            // echo "Already followed $url \n";
            return null;
        }

        $this->count++;
        if ($this->count > $this->maxCount) {
            $message = sprintf(
                "Skipping %s as %s urls already checked.",
                $urlToCheck->getUrl(),
                $this->maxCount
            );
            
            $this->statusWriter->write($message);
            return null;
        }

        $this->urlsToCheck[$url] = null;
        return $this->fetchURL($urlToCheck);
    }
}