<?php

namespace SiteTool\EventProcessor;

use SiteTool\UrlToCheck;
use Amp\Artax\Response;
use SiteTool\EventManager;
use SiteTool\Event\ResponseReceived;
use SiteTool\Event\FoundUrlToFollow;
use SiteTool\Event\ResponseError;
use Amp\Artax\Client as ArtaxClient;
use SiteTool\Writer\OutputWriter;

class FetchUrl
{
    /** @var  callable */
    private $responseReceivedTrigger;

    /** @var  callable */
    private $responseErrorTrigger;
    
    private $switchName = "Fetch the URL";
    
            /** @var URLToCheck[] */
    private $urlsToCheck = [];
    
    private $count = 0;

    /**
     * @var ArtaxClient
     */
    private $artaxClient;
    
    /** @var OutputWriter */
    private $outputWriter;

    private $maxCount;


    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter,
        ArtaxClient $artaxClient
    ) {
        $this->outputWriter = $outputWriter;
        $this->maxCount = 10000;
        $this->artaxClient = $artaxClient;
        $eventManager->attachEvent(FoundUrlToFollow::class, [$this, 'followURL'], $this->switchName);
        $this->responseReceivedTrigger = $eventManager->createTrigger(ResponseReceived::class, $this->switchName);
        $this->responseErrorTrigger = $eventManager->createTrigger(ResponseError::class, $this->switchName);
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    public function followURL(FoundUrlToFollow $foundUrlToFollow)
    {
        $urlToCheck = $foundUrlToFollow->urlToCheck;
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
        $this->fetchURL($urlToCheck);
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    function fetchURL(URLToCheck $urlToCheck)
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
        ) use ($urlToCheck, $fullURL) {
            $this->resultFetched($e, $response, $urlToCheck, $fullURL);
        };

        $promise->when($analyzeResult);
    }


    function resultFetched(
        \Exception $e = null,
        Response $response = null,
        UrlToCheck $urlToCheck,
        $fullURL
    ) {
        if ($response !== null) {
            $fn = $this->responseReceivedTrigger;
            $fn(new ResponseReceived($response, $urlToCheck));
            return;
        }
    }
}
