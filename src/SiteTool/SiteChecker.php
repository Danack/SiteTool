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

    const FOUND_URL         = 'found_url';
    const FOUND_URL_TO_FOLLOW = 'found_url_to_scan';
    const REQUEST_ERROR     = 'request_error';
    const PARSING_ERROR     = 'parsing_error';
    
    
    const SKIPPING_LINK_DUE_TO_DOMAIN = 'skipping_link_due_to_domain';
    
    /** @var URLToCheck[] */
    private $urlsToCheck = [];

    private $errors = 0;
    
    private $count = 0;

    /**
     * @var ArtaxClient
     */
    private $artaxClient;
    
    /** @var OutputWriter */
    private $outputWriter;

    /** @var EventManager */
    private $eventManager;
    
    private $maxCount;
    
    function __construct(
        ArtaxClient $artaxClient,
        OutputWriter $outputWriter,
        EventManager $eventManager,
        //ContentTypeEventList $contentTypeEvent,
        $maxCount
    ) {
        $this->artaxClient = $artaxClient;
        $this->outputWriter = $outputWriter;
        $this->eventManager = $eventManager;
        $this->maxCount = $maxCount;
        //$this->contentTypeEvent = $contentTypeEvent;

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
        $this->outputWriter->write(
            OutputWriter::PROGRESS,
            $message
        );
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
        $this->outputWriter->write(
            OutputWriter::CRAWL_RESULT,
            $urlToCheck->getUrl(),
            $status,
            $urlToCheck->getReferrer()
        );

        if ($status != 200 && $status != 420 && $status != 202) {
            if ($first == true) {
                $first = false;
            }
            else {
                $this->outputWriter->write(
                    OutputWriter::PROGRESS | OutputWriter::ERROR,
                    "Status $status is not OK for " . $urlToCheck->getUrl() . " ",
                    $urlToCheck->getReferrer()
                );
                $this->outputWriter->write(
                    OutputWriter::PROGRESS | OutputWriter::ERROR,
                    "Status $status is not OK for ",
                    $urlToCheck->getUrl(),
                    $urlToCheck->getReferrer()
                );
                
                $this->errors++;
                return;
            }
        }

        $this->eventManager->trigger(SiteChecker::RESPONSE_OK, null, [$response, $urlToCheck]);
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
        $this->outputWriter->write(
            OutputWriter::PROGRESS,
            "Fetching $fullURL from referrer " . $urlToCheck->getReferrer()
        );
        $promise = $this->artaxClient->request($fullURL);

        $analyzeResult = function(
            \Exception $e = null,
            Response $response = null
        ) use ($urlToCheck, $fullURL) {
            $this->outputWriter->write(
                OutputWriter::PROGRESS,
                "Got $fullURL"
            );
            $this->analyzeResult($e, $response, $urlToCheck, $fullURL);
        };

        $promise->when($analyzeResult);
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    public function followURL(URLToCheck $urlToCheck)
    {
        $url = $urlToCheck->getUrl();
        if (array_key_exists($url, $this->urlsToCheck) === true) {

            return null;
        }

        $this->count++;
        if ($this->count > $this->maxCount) {
            $message = sprintf(
                "Skipping %s as %s urls already checked.",
                $urlToCheck->getUrl(),
                $this->maxCount
            );
            $this->outputWriter->write(
                OutputWriter::PROGRESS,
                $message
            );

            return null;
        }

        $this->urlsToCheck[$url] = null;
        $this->fetchURL($urlToCheck);
    }
}