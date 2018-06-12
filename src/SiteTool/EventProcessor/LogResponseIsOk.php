<?php


namespace SiteTool\EventProcessor;

use SiteTool\EventManager;
use SiteTool\Writer\OutputWriter;
use SiteTool\Event\ResponseReceived;

class LogResponseIsOk implements Relay
{
    /** @var OutputWriter */
    private $outputWriter;

    /** @var EventManager */
    private $eventManager;

    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $this->eventManager = $eventManager;
        $eventManager->attachEvent(ResponseReceived::class, [$this, 'checkStatus'], 'Log response');
        $this->outputWriter = $outputWriter;
    }

    public function checkStatus(ResponseReceived $responseReceivedData)
    {
        $response = $responseReceivedData->getResponse();
        $fullURL = $responseReceivedData->getUrlToCheck()->getUrl();
        
        $status = $response->getStatus();
        if ($status === 200) {
            $this->outputWriter->write(
                OutputWriter::PROGRESS,
                "URL $fullURL is 200 ok"
            );
            return;
        }

        $this->outputWriter->write(
            OutputWriter::PROGRESS | OutputWriter::CHECK_RESULT,
            $status, $fullURL
        );
    }

    public function getAsyncWorkers()
    {
        return [];
    }
}
