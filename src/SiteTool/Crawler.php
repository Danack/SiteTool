<?php


namespace SiteTool;

use Amp\Artax\Client as ArtaxClient;
use SiteTool\Printer\HTMLPrinter;

class Crawler
{
    private $domainName;
    
    public function __construct($domainName)
    {
        $this->domainName = $domainName;
    }

    public function run(
        ArtaxClient $artaxClient,
        ResultWriter $resultWriter,
        StatusWriter $statusWriter,
        $maxCount
    ) {
        $crawlerConfig = new CrawlerConfig(
            'http',
            $this->domainName,
            '/'
        );

        $siteChecker = new SiteChecker($crawlerConfig, $artaxClient, $resultWriter, $statusWriter, $maxCount);
        $fn = function() use ($siteChecker) {
            $start = new URLToCheck('http://' . $this->domainName . '/', '/');
            $siteChecker->checkURL($start);
        };

        \Amp\run($fn);
        
        $statusWriter->write("fin.");
    }
}
