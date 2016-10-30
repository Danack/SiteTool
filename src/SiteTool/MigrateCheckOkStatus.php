<?php


namespace SiteTool;

use Amp\Artax\Response;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\OutputWriter;

class MigrateCheckOkStatus
{
    //private $migrationResultWriter;
    
    /** @var OutputWriter  */
    private $outputWriter;
    
    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $eventManager->attach(SiteChecker::RESPONSE_RECEIVED, [$this, 'checkStatusEvent']);
        $this->outputWriter = $outputWriter;
    }

    public function checkStatusEvent(Event $event)
    {
        list($response, $fullURL) = $event->getParams();
        $this->checkStatus($response, $fullURL);
    }

    public function checkStatus(Response $response, $fullURL)
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
            OutputWriter::PROGRESS | OutputWriter::MIGRATION_RESULT,
            $status, $fullURL
        );
    }
}
