<?php

namespace SiteTool\Processor;

use Amp\Artax\Response;
use SiteTool\SiteChecker;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\Writer\OutputWriter;

class SiteCheckOkStatus
{
    /** @var OutputWriter  */
    private $outputWriter;
    
    public function __construct(
        OutputWriter $outputWriter
    ) {
        $this->outputWriter = $outputWriter;
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
            OutputWriter::PROGRESS | OutputWriter::CHECK_RESULT,
            $status, $fullURL
        );
    }
}
