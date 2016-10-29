<?php


namespace SiteTool;

use Amp\Artax\Response;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\StatusWriter;
use SiteTool\MigrationResultWriter\FileMigrationResultWriter;

class MigrateCheckOkStatus
{
    private $migrationResultWriter;
    
    public function __construct(
        EventManager $eventManager,
        StatusWriter $statusWriter,
        FileMigrationResultWriter $fileMigrationResultWriter
    ) {
        $this->migrationResultWriter = $fileMigrationResultWriter;
        $eventManager->attach(SiteChecker::RESPONSE_RECEIVED, [$this, 'checkStatusEvent']);
        $this->statusWriter = $statusWriter;
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
            $this->statusWriter->write("URL $fullURL is 200 ok");
            //$this->migrationResultWriter->write("URL $fullURL is 200 ok");
            return;
        }
        $this->statusWriter->write("URL $fullURL is status $status");
        $this->migrationResultWriter->write($status, $fullURL);
    }
}
