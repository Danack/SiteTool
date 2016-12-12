<?php


namespace SiteTool\Processor;

use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\Response;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\OutputWriter;
use SiteTool\UrlToCheck;
use SiteTool\Event\LinkFetcherEvent;


class LinkFetcher
{

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

//    /** @var EventManager */
//    private $eventManager;
//    
    private $maxCount;
    
    //private $responseOkEvent;
    
    
    function __construct(OutputWriter $outputWriter, ArtaxClient $artaxClient)
    {
        $this->outputWriter = $outputWriter;
        $this->maxCount = 10000;
        $this->artaxClient = $artaxClient;
    }
    
    /**
     * @param URLToCheck $urlToCheck
     */
    function fetchURL(URLToCheck $urlToCheck, LinkFetcherEvent $linkFetcherEvent)
    {
        $fullURL = $urlToCheck->getUrl();
        
        echo "$fullURL \n";
        
        $this->outputWriter->write(
            OutputWriter::PROGRESS,
            "Fetching $fullURL from referrer " . $urlToCheck->getReferrer()
        );
        $promise = $this->artaxClient->request($fullURL);

        $analyzeResult = function(
            \Exception $e = null,
            Response $response = null
        ) use ($urlToCheck, $fullURL, $linkFetcherEvent) {
            
            $linkFetcherEvent->resultFetched($e, $response, $urlToCheck, $fullURL);
            
//            $this->outputWriter->write(
//                OutputWriter::PROGRESS,
//                "Processing $fullURL"
//            );
//            $this->analyzeResult($e, $response, $urlToCheck, $fullURL);
        };

        $promise->when($analyzeResult);
    }
    
    
        /**
     * @param URLToCheck $urlToCheck
     */
    public function followURL(
        URLToCheck $urlToCheck,
        LinkFetcherEvent $linkFetcherEvent
    ) {
        $url = $urlToCheck->getUrl();
        if (array_key_exists($url, $this->urlsToCheck) === true) {
            return null;
        }

        $this->count++;
        if ($this->count > $this->maxCount) {

            $message = sprintf(
                "Skipping %s as limit of %d urls has been reached.",
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
        $this->fetchURL($urlToCheck, $linkFetcherEvent);
    }
    
}
