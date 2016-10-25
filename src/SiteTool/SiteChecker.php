<?php

namespace SiteTool;

use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\SocketException;
use Amp\Artax\Response;
use FluentDOM\Document;
use FluentDOM\Element;



class SiteChecker
{
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
    
    private $maxCount;
    
    function __construct(
        CrawlerConfig $crawlerConfig,
        ArtaxClient $artaxClient,
        ResultWriter $resultWriter,
        StatusWriter $statusWriter,
        $maxCount
    ) {
        $this->crawlerConfig = $crawlerConfig;
        $this->artaxClient = $artaxClient;
        $this->rules = new Rules($crawlerConfig, $statusWriter);
        $this->resultWriter = $resultWriter;
        $this->statusWriter = $statusWriter;
        
        $this->maxCount = $maxCount;
        
        // This is nice.
        libxml_use_internal_errors(true);
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
        if ($e) {
            $this->handleException($e, $response, $fullURL);
            return;
        }
    
        $status = $response->getStatus();
        
        $this->resultWriter->write(
            $urlToCheck->getUrl(),
            $status,
            $urlToCheck->getReferrer(),
            substr($response->getBody(), 0, 200)
        );

        if ($status != 200 && $status != 420 && $status != 202) {
            $this->statusWriter->write("Status $status is not OK for " . $urlToCheck->getUrl());
            $this->errors++;
            return null;
        }
    
        $this->analyzeResponse($response, $urlToCheck);
    }

    /**
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @return null
     * @throws \Exception
     */
    public function analyzeResponse(Response $response, UrlToCheck $urlToCheck)
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

        switch ($contentType) {
            case ('text/html'): {
                $this->analyzeHtmlBody($urlToCheck, $response->getBody());
                break;
            }

            case ('text/plain'): {
                return null;
                break;
            }

            case ('application/octet-stream') :
            case ('image/gif') :
            case ('image/jpeg') :
            case ('image/jpg') :
            case ('image/vnd.adobe.photoshop') :
            case ('image/png') :
            case ('application/atom+xml') : {
                return null;
            }

            default: {
                // throw new \Exception("Unrecognised content-type $contentType");
                echo "Unrecognised content-type $contentType";
            }
        }
    }

    function getErrorCount()
    {
        return $this->errors;
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    function fetchURL(URLToCheck $urlToCheck)
    {
        $fullURL = $urlToCheck->getUrl();
        $this->statusWriter->write("Adding $fullURL from referrer " . $urlToCheck->getReferrer());
        $promise = $this->artaxClient->request($fullURL);

        $analyzeResult = function(
            \Exception $e = null,
            Response $response = null
        ) use ($urlToCheck, $fullURL) {
            $this->statusWriter->write("Getting $fullURL");
            return $this->analyzeResult($e, $response, $urlToCheck, $fullURL);
        };

        $promise->when($analyzeResult);
    }

    /**
     * @param Element $element
     * @param $referrer
     */
    function parseLinkResult(Element $element, $referrer)
    {
        $href = $element->getAttribute('href');

        $this->addLinkToProcess($href, $referrer);
    }

    /**
     * @param Element $element
     * @param $referrer
     */
    function parseImgResult(Element $element, $referrer)
    {
        $href = $element->getAttribute('src');
        $this->addLinkToProcess($href, $referrer);
    }

    /**
     * @param $href
     * @param $referrer
     */
    function addLinkToProcess($href, $referrer)
    {
        $urlToCheck = $this->rules->getUrlToCheck($href, $referrer);
        if (!$urlToCheck) {
            return;
        }
        $this->checkURL($urlToCheck);
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    function checkURL(URLToCheck $urlToCheck)
    {
        $url = $urlToCheck->getUrl();
        if (array_key_exists($url, $this->urlsToCheck)) {
            return;
        }
        
        $this->count++;

        if ($this->count > $this->maxCount) {
            $this->statusWriter->write("Skipping " . $urlToCheck->getUrl() . " as $maxCount urls already checked.");
            return null;
        }

        $this->urlsToCheck[$url] = null;
        $this->fetchURL($urlToCheck);
    }

    /**
     * @param URLToCheck $urlToCheck
     * @param $body
     */
    function analyzeHtmlBody(URLToCheck $urlToCheck, $body)
    {
        $ok = false;
        $path = $urlToCheck->getUrl();

        try {
            $document = new Document();
            $document->loadHTML($body);
            $linkClosure = function (Element $element) use ($urlToCheck) {
                $this->parseLinkResult($element, $urlToCheck->getUrl());
            };
            $imgClosure = function (Element $element) use ($urlToCheck) {
                $this->parseImgResult($element, $urlToCheck->getUrl());
            };
    
            $document->find('//a')->each($linkClosure);
            //$document->find('//img')->each($imgClosure);
            $ok = true;
        }
        catch (SocketException $se) {
            $message = "Artax\\SocketException on $path - ".$se->getMessage(). " Exception type is ".get_class($se);
            $this->resultWriter->write(
                $path,
                500,
                $urlToCheck->getReferrer(),
                $message
            );
        }
        catch(\InvalidArgumentException $iae) {
            $message = "Fluent dom exception on $path - ".$iae->getMessage(). " Exception type is ".get_class($iae);
            $this->resultWriter->write(
                $path,
                500,
                $urlToCheck->getReferrer(),
                $message
            );
        }
        catch(\Exception $e) {
            $message = "Error getting $path - " . $e->getMessage() . " Exception type is " . get_class($e);
            $message .= $e->getTraceAsString();
            
            $this->resultWriter->write(
                $path,
                500,
                $urlToCheck->getReferrer(),
                $message
            );
        }

        if ($ok != true) {
            $this->errors++;
        }
    }
}