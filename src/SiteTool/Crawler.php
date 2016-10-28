<?php


namespace SiteTool;

use Amp\Artax\Client as ArtaxClient;
use SiteTool\ErrorWriter\FileErrorWriter;
use SiteTool\Printer\HTMLPrinter;
use SiteTool\ResultWriter\FileResultWriter;
use Zend\EventManager\EventManager;

class Crawler
{
    private $domainName;
    
    public function __construct($domainName)
    {
        $this->domainName = $domainName;
    }

    public function run(
        ArtaxClient $artaxClient,
        //ResultWriter $resultWriter,
        StatusWriter $statusWriter,
        EventManager $eventManager,
        $maxCount
    ) {
        $crawlerConfig = new CrawlerConfig(
            'http',
            $this->domainName,
            '/'
        );
        
        $resultWriter = new FileResultWriter("output.txt");
        $errorWriter = new FileErrorWriter("error.txt");

        $siteChecker = new SiteChecker(
            $crawlerConfig,
            $artaxClient,
            $resultWriter,
            $statusWriter,
            $errorWriter,
            $eventManager,
            $maxCount
        );
        $fn = function() use ($siteChecker) {
            $start = new URLToCheck('http://' . $this->domainName . '/', '/');
            $siteChecker->checkURL($start);
        };

        \Amp\run($fn);
        
        $statusWriter->write("fin.");
    }
}
