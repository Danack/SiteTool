<?php


namespace SiteTool\Event\SiteCheckOkStatusEvent;

use SiteTool\Event\SiteCheckOkStatusEvent;
use SiteTool\SiteChecker;
use Zend\EventManager\Event;
use SiteTool\EventManager;
use SiteTool\Writer\OutputWriter;
use Amp\Artax\Response;


class SiteCheckOkStatusZendEvent implements SiteCheckOkStatusEvent
{
    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $this->eventManager = $eventManager;
        $eventManager->attach(SiteChecker::RESPONSE_RECEIVED, [$this, 'checkStatusEvent'], 'SiteCheckOk');
        $this->outputWriter = $outputWriter;
    }

    public function checkStatusEvent(Event $event)
    {
        list($response, $fullURL) = $event->getParams();
        $this->checkStatus($response, $fullURL);
    }

    private function checkStatus(Response $response, $fullURL)
    {
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
}
