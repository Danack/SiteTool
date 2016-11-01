<?php

namespace SiteTool\Command;

use Auryn\Injector;
use SiteTool\ResultReader;
use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\Response;
use SiteTool\SiteChecker;
use Zend\EventManager\EventManager;
use SiteTool\Writer\OutputWriter;

class Check
{
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
        ResultReader $resultReader,
        ArtaxClient $artaxClient,
        OutputWriter $outputWriter,
        EventManager $eventManager
    ) {
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
        $plugins[] = $injector->make(\SiteTool\Processor\SiteCheckOkStatus::class);
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

            $url = $result->url;
            
            $this->outputWriter->write(
                OutputWriter::PROGRESS,
                "Checking " . $result->url
            );

            $analyzeResult = function(
                \Exception $e = null,
                Response $response = null
            ) use ($url) {
                $this->analyzeResult($e, $response, $url);
            };

            $promise = $this->artaxClient->request($url);
            $promise->when($analyzeResult);
        }
    }
}
