<?php

namespace SiteTool;

use SiteTool\ResultReader;
use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\Response;
use SiteTool\ResultWriter\FileResultWriterFactory;
use SiteTool\StatusWriter\StdoutStatusWriter;
use SiteTool\ResultReader\StandardResultReader;

class MigrateCheck
{
    private $oldDomainName;
    private $newDomainName;
    
    /** @var ResultReader  */
    private $resultReader;
    
    /** @var StatusWriter  */
    private $statusWriter;

    /**
     * @var ArtaxClient
     */
    private $artaxClient;
    
    private $errorCount;
    
    public function __construct(
        $oldDomainName,
        $newDomainName,
        //ResultReader $resultReader,
        ArtaxClient $artaxClient,
        FileResultWriterFactory $fileResultWriterFactory
    ) {
        $this->oldDomainName = $oldDomainName; 
        $this->newDomainName = $newDomainName;
        $this->resultReader = //$resultReader;
        
        new StandardResultReader('output.txt');
        
        $this->artaxClient = $artaxClient;
        $this->resultWriter = $fileResultWriterFactory->create("migrate_check.txt");
        $this->statusWriter = new StdoutStatusWriter();
    }

    /**
     * @param \Exception $e
     * @param Response $response
     * @param $fullURL
     */
    public function analyzeResult(\Exception $e = null, Response $response = null, $fullURL)
    {
        if ($e) {
            $this->handleException($e, $response, $fullURL);
            return;
        }

        $status = $response->getStatus();
        if ($status === 200) {
            $this->statusWriter->write("URL $fullURL is 200 ok");
            return;
        }
        $this->statusWriter->write("URL $fullURL is status $status");
        $this->errorCount++;
        $this->resultWriter->write($status, $fullURL);
    }

    /**
     * @param \Exception $e
     * @param Response $response
     * @param $fullURL
     * @return null
     */
    function handleException(\Exception $e, Response $response = null, $fullURL)
    {
        $message = "Something went wrong for $fullURL : " . $e->getMessage();
        if ($response) {
            $message .= "Headers " . var_export($response->getAllHeaders(), true);
        }
        $this->statusWriter->write($message);
        $this->errorCount++;

        return null;
    }

    /**
     * 
     */
    public function run()
    {
        \Amp\run([$this, 'check']);
        echo "Completed with " . $this->errorCount . " errors";
    }

    /**
     * 
     */
    public function check()
    {
        $results = $this->resultReader->readAll();

        foreach ($results as $result) {
            $newUrl = str_replace($this->oldDomainName, $this->newDomainName, $result->url);
            echo $newUrl . " \n";
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
