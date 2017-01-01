<?php

namespace SiteTool\Processor;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;
use SiteTool\EventManager;
use SiteTool\Processor\Data\ResponseReceived;
use SiteTool\Processor\Data\ResponseOk;
use SiteTool\Writer\OutputWriter;

class CheckResponseIsOk
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

    /**
     * @param \Exception $e
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @param $fullURL
     * @return null|void
     * @throws \Exception
     */
    function analyzeResult(ResponseReceived $responseReceivedData)
    {
        $response = $responseReceivedData->response;
        $urlToCheck = $responseReceivedData->urlToCheck;

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
            
            //$this->errors++;
            return;
        }

        $this->responseOk($response, $urlToCheck);
    }

    public function responseOk(Response $response, UrlToCheck $urlToCheck)
    {
        $fn = $this->responseOkTrigger;
        $fn(new ResponseOk($response, $urlToCheck));
    }
}
