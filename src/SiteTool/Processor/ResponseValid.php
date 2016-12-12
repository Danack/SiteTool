<?php


namespace SiteTool\Processor;


use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\Response;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\OutputWriter;
use SiteTool\UrlToCheck;
use SiteTool\Event\ResponseOkEvent;

class ResponseValid
{
    /** @var URLToCheck[] */
    private $urlsToCheck = [];

    private $errors = 0;
    
    private $count = 0;

//    /**
//     * @var ArtaxClient
//     */
//    private $artaxClient;
//    
    /** @var OutputWriter */
    private $outputWriter;

//    /** @var EventManager */
//    private $eventManager;
    
    private $maxCount;
    
    private $responseOkEvent;
    
    function __construct(
        ArtaxClient $artaxClient,
        OutputWriter $outputWriter,
        EventManager $eventManager,
        $maxCount,
        $foundUrlToFollowEvent,
        $responseOkEvent
    ) {
        //$this->artaxClient = $artaxClient;
        $this->outputWriter = $outputWriter;
        //$this->eventManager = $eventManager;
        $this->maxCount = $maxCount;

        // This is fine.
        libxml_use_internal_errors(true);

        //$eventManager->attach($foundUrlToFollowEvent, [$this, 'followURLEvent']);
        //$this->responseOkEvent = $responseOkEvent;
    }
    
    /**
     * @param \Exception $e
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @param $fullURL
     * @return null|void
     * @throws \Exception
     */
    function analyzeResult(
        \Exception $e = null, 
        Response $response = null,
        UrlToCheck $urlToCheck,
        $fullURL,
        ResponseOkEvent $responseOkEvent
    ) {
        static $first = true;
        
        if ($e) {
            $this->handleException($e, $response, $fullURL, $urlToCheck->getReferrer());
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

        $responseOkEvent->responseOk($response, $urlToCheck);
        //$this->eventManager->trigger($this->responseOkEvent, null, [$response, $urlToCheck]);
    }

    function getErrorCount()
    {
        return $this->errors;
    }
    
    
        /**
     * @param \Exception $e
     * @param Response $response
     * @param $fullURL
     * @return null
     */
    function handleException(\Exception $e, Response $response = null, $fullURL, $referrer)
    {
        $message = "RequestException for $fullURL : " . $e->getMessage() . " from referrer " . $referrer;
        if ($response) {
            $message .= "Headers " . var_export($response->getAllHeaders(), true);
        }
        $this->outputWriter->write(
            OutputWriter::PROGRESS | OutputWriter::ERROR,
            $message
        );
        $this->errors++;

        return null;
    }
    
//           /**
//     * @param URLToCheck $urlToCheck
//     */
//    function fetchURL(URLToCheck $urlToCheck)
//    {
//        $fullURL = $urlToCheck->getUrl();
//        $this->outputWriter->write(
//            OutputWriter::PROGRESS,
//            "Fetching $fullURL from referrer " . $urlToCheck->getReferrer()
//        );
//        $promise = $this->artaxClient->request($fullURL);
//
//        $analyzeResult = function(
//            \Exception $e = null,
//            Response $response = null
//        ) use ($urlToCheck, $fullURL) {
//            $this->outputWriter->write(
//                OutputWriter::PROGRESS,
//                "Processing $fullURL"
//            );
//            $this->analyzeResult($e, $response, $urlToCheck, $fullURL);
//        };
//
//        $promise->when($analyzeResult);
//    }
//    
//    
//        /**
//     * @param URLToCheck $urlToCheck
//     */
//    public function followURL(URLToCheck $urlToCheck)
//    {
//        $url = $urlToCheck->getUrl();
//        if (array_key_exists($url, $this->urlsToCheck) === true) {
//            return null;
//        }
//
//        $this->count++;
//        if ($this->count > $this->maxCount) {
//            $message = sprintf(
//                "Skipping %s as %s urls already checked.",
//                $urlToCheck->getUrl(),
//                $this->maxCount
//            );
//            $this->outputWriter->write(
//                OutputWriter::PROGRESS,
//                $message
//            );
//
//            return null;
//        }
//
//        $this->urlsToCheck[$url] = null;
//        $this->fetchURL($urlToCheck);
//    }
    
}
