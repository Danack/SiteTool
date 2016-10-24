<?php


namespace SiteTool;

use Amp\Artax\Client as ArtaxClient;
use SiteTool\Printer\HTMLPrinter;

class Crawler
{
    public function run(ArtaxClient $artaxClient)
    {
        $site = "www.olivemagazine.com";
        
        $crawlerConfig = new CrawlerConfig(
            'http',
            "www.olivemagazine.com",
            '/'
        );
        
        $siteChecker = new SiteChecker($crawlerConfig, $artaxClient);

        $fn = function() use ($siteChecker) {
            $start = new URLToCheck('http://www.olivemagazine.com/', '/');
            $siteChecker->checkURL($start);
        };

        //$reactor->run();
        \Amp\run($fn);
        
        echo "fin";
        
        $printer = new HTMLPrinter($siteChecker->getResults(), $site);

        $outputStream = fopen("./checkResults.html", "w");
        $printer->output($outputStream);
        fclose($outputStream);
        echo "Check complete. Found ".$siteChecker->getURLCount()." URIs with ".$siteChecker->getErrorCount()." errors.";

    }
}
