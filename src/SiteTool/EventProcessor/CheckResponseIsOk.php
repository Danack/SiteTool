<?php

namespace SiteTool\EventProcessor;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;
use SiteTool\EventManager;
use SiteTool\Event\ResponseReceived;
use SiteTool\Event\ResponseOk;
use SiteTool\Writer\OutputWriter;

class CheckResponseIsOk implements Relay
{
    /** @var  \callable */
    private $responseOkTrigger;

    private $switchName = "Is the response OK?";
    
    /** @var OutputWriter */
    private $outputWriter;
    
    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $this->outputWriter = $outputWriter;
        $eventManager->attachEvent(ResponseReceived::class, [$this, 'analyzeResult'], $this->switchName);
        $this->responseOkTrigger = $eventManager->createTrigger(ResponseOk::class, $this->switchName);
    }


    public function analyzeResult(ResponseReceived $responseReceived)
    {
        $urlToCheck = $responseReceived->getUrlToCheck();
        $status = $responseReceived->getResponse()->getStatus();
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
            
            //$this->errors++;
            return;
        }

        $fn = $this->responseOkTrigger;
        $fn(new ResponseOk($responseReceived, $urlToCheck));
    }

    public function getAsyncWorkers()
    {
        return [];
    }
}
