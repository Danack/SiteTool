<?php

namespace SiteTool;

use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\SocketException;
use Amp\Artax\Response;
use FluentDOM\Document;
use FluentDOM\Element;



class SiteChecker
{
    /**
     * @var URLResult[]
     */
    private $urlsChecked = [];
    
    /** @var URLToCheck[] */
    private $urlsToCheck = [];
    
    // private $siteURL;
    
    /** @var CrawlerConfig  */
    private $crawlerConfig;
    
    private $count = 0;

    private $errors = 0;
    
    /** @var Rules  */
    private $rules;

    /**
     * @var ArtaxClient
     */
    private $artaxClient;
    
    /** @var Output  */
    private $output;
    
    function __construct(CrawlerConfig $crawlerConfig, ArtaxClient $artaxClient)
    {
        $this->crawlerConfig = $crawlerConfig;
        $this->artaxClient = $artaxClient;
        $this->rules = new Rules($crawlerConfig);
        
        $this->output = new Output();
        
        // This is nice.
        libxml_use_internal_errors(true);
    }
    
    function handleException(\Exception $e, Response $response = null, $fullURL)
    {
        echo "Something went wrong for $fullURL : ".$e->getMessage()."\n";
        if ($response) {
            var_dump($response->getAllHeaders());
        }
        $this->errors++;
        return null;
    }
    
    
    function analyzeResult(\Exception $e = null, Response $response = null, UrlToCheck $urlToCheck, $fullURL)
    {
        if ($e) {
            $this->handleException($e, $response, $fullURL);
            return;
        }
    
        $status = $response->getStatus();
        $this->urlsChecked[] = new URLResult(
            $urlToCheck->getUrl(),
            $status,
            $urlToCheck->getReferrer(),
            substr($response->getBody(), 0, 200)
        );
    
        if ($status != 200 && $status != 420 && $status != 202) {
            echo "Status is not ok for " . $urlToCheck->getUrl()."\n";
            $this->errors++;
            return null;
        }
    
        $this->analyzeResponse($response, $urlToCheck);
    }
    
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

    function getURLCount()
    {
        return count($this->urlsChecked);
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
        $this->count++;
        
        if ($this->count > 2000) {
            return;
        }
        $fullURL = $urlToCheck->getUrl();        
        $this->output->gettingUrl($fullURL);
        $promise = $this->artaxClient->request($fullURL);

        $analyzeResult = function(
            \Exception $e = null,
            Response $response = null
        ) use ($urlToCheck, $fullURL) {
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
        
//        if ($this->rules->shouldFollowURLToCheck($urlToCheck) == false) {
//            return;
//        }
        
        
//        if (strpos($urlToCheck->getUrl(), '/') !== 0) {
//            $this->urlsToCheck[$url] = new URLResult($url, 200, $urlToCheck->getReferrer());
//            return;
//        }

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
            $this->urlsChecked[] = new URLResult($path, 500, "Artax\\SocketException on $path - ".$se->getMessage(). " Exception type is ".get_class($se));
        }
        catch(\InvalidArgumentException $iae) {
            $this->urlsChecked[] = new URLResult($path, 500, "Fluent dom exception on $path - ".$iae->getMessage(). " Exception type is ".get_class($iae));
        }
        catch(\Exception $e) {
            //echo "Error getting $path - ".$e->getMessage(). " Exception type is ".get_class($e)." \n";
            $this->urlsChecked[] = new URLResult($path, 500, "Error getting $path - ".$e->getMessage(). " Exception type is ".get_class($e));
            
            file_put_contents("test.html", $body);
            
            echo $e->getTraceAsString();
        }

        if ($ok != true) {
            $this->errors++;
        }
    }

    /**
     * @return URLResult[]
     */
    function getResults()
    {
        return $this->urlsChecked;
    }
}