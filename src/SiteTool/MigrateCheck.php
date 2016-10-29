<?php

namespace SiteTool;

use Auryn\Injector;
use SiteTool\ResultReader;
use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\Response;
use SiteTool\MigrationResultWriter\FileMigrationResultWriter;
use SiteTool\StatusWriter;
use Zend\EventManager\EventManager;
use SiteTool\ErrorWriter;

class MigrateCheck
{
    private $oldDomainName;
    private $newDomainName;
    
    /** @var ResultReader  */
    private $resultReader;
    
    /** @var StatusWriter  */
    private $statusWriter;

    /** @var FileMigrationResultWriter */
    private $fileMigrationResultWriter;
    
    /** @var ErrorWriter */
    private $errorWriter;
    
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
        StatusWriter $statusWriter,
        FileMigrationResultWriter $fileMigrationResultWriter,
        ErrorWriter $errorWriter,
        EventManager $eventManager
    ) {
        $this->oldDomainName = $oldDomainName; 
        $this->newDomainName = $newDomainName;
        $this->resultReader = $resultReader;
        $this->eventManager = $eventManager;
        $this->errorWriter = $errorWriter;

        $this->artaxClient = $artaxClient;
        $this->fileMigrationResultWriter = $fileMigrationResultWriter;
        $this->statusWriter = $statusWriter;
    }

    /**
     * 
     */
    public function run(Injector $injector)
    {
        $plugins[] = $injector->make(\SiteTool\MigrateCheckOkStatus::class);
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
            $this->statusWriter->write($message);
            $this->errorWriter->write($message);
            return;
        }

        if ($response === null) {
            $message = "Failed to read response for $fullURL";
            $this->statusWriter->write($message);
            $this->errorWriter->write($message);
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
            $this->statusWriter->write("Checking $newUrl");
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
