<?php

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\ResultReader;
use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\Response;
use SiteTool\SiteChecker;
use Zend\EventManager\EventManager;
use SiteTool\Writer\OutputWriter;

class MigrateCheck
{
    private $oldDomainName;
    private $newDomainName;
    
    /** @var ResultReader  */
    private $resultReader;

    /** @var OutputWriter */
    private $outputWriter;
    
    /**
     * @var ArtaxClient
     */
    private $artaxClient;

    /** @var \Zend\EventManager\EventManager */
    private $eventManager;
    
    public function __construct(
        $oldDomainName,
        $newDomainName,
        ResultReader $resultReader,
        ArtaxClient $artaxClient,
        OutputWriter $outputWriter,
        EventManager $eventManager
    ) {
        $this->oldDomainName = $oldDomainName; 
        $this->newDomainName = $newDomainName;
        $this->resultReader = $resultReader;
        $this->eventManager = $eventManager;
        $this->outputWriter = $outputWriter;
        $this->artaxClient = $artaxClient;
    }

    /**
     * 
     */
    public function run(Injector $injector)
    {
        $plugins[] = $injector->make(\SiteTool\Processor\MigrateCheckOkStatus::class);
        \Amp\run([$this, 'check']);
    }


    /**
     * @param \Exception $e
     * @param Response $response
     * @param $fullURL
     */
    public function analyzeResult(\Exception $e = null, Response $response = null, $fullURL)
    {
        if ($e) {
            $message = "Something went wrong for $fullURL : " . $e->getMessage();
            if ($response) {
                $message .= "Headers " . var_export($response->getAllHeaders(), true);
            }

            $this->outputWriter->write(
                OutputWriter::PROGRESS | OutputWriter::ERROR,
                $message
            );
            return;
        }

        if ($response === null) {
            $message = "Failed to read response for $fullURL";
            $this->outputWriter->write(
                OutputWriter::ERROR | OutputWriter::PROGRESS,
                $message
            );
            return;
        }

        $this->eventManager->trigger(SiteChecker::RESPONSE_RECEIVED, null, [$response, $fullURL]);
    }

    /**
     * 
     */
    public function check()
    {
        $results = $this->resultReader->readAll();
        foreach ($results as $result) {
            if ($result->status != 200) {
                continue;
            }

            $newUrl = str_replace($this->oldDomainName, $this->newDomainName, $result->url);
            $this->outputWriter->write(
                OutputWriter::PROGRESS,
                "Checking $newUrl"
            );
            $promise = $this->artaxClient->request($newUrl);

            $analyzeResult = function(
                \Exception $e = null,
                Response $response = null
            ) use ($newUrl) {
                $this->analyzeResult($e, $response, $newUrl);
            };

            $promise->when($analyzeResult);
        }
    }
}
