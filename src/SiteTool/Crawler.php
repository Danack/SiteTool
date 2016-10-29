<?php

namespace SiteTool;

use SiteTool\ResultWriter\FileResultWriter;
use SiteTool\ResultWriter;
use Auryn\Injector;
use SiteTool\ErrorWriter;
use SiteTool\StatusWriter\StdoutStatusWriter;
use SiteTool\StatusWriter;

class Crawler
{
    private $initialUrl;
    
    private $domainName;
    
    public function __construct($initialUrl)
    {
        $this->initialUrl = $initialUrl;
        $urlParts = parse_url($initialUrl);
        if (array_key_exists('host', $urlParts) === false) {
            echo "Could not determine domain name from " . $initialUrl . "\n";
            exit(-1);
        }

        $this->domainName = $urlParts['host'];
        $this->initialPath = '/';

        if (array_key_exists('host', $urlParts) === true) {
            $this->initialPath = $urlParts['path'];
        }
    }

    public function run(Injector $injector)
    {
        $resultWriter = new FileResultWriter("output.txt");
        $injector->alias(ResultWriter::class, get_class($resultWriter));
        $injector->share($resultWriter);

        $errorWriter = new \SiteTool\ErrorWriter\FileErrorWriter("error.txt");
        $injector->alias(ErrorWriter::class, \SiteTool\ErrorWriter\FileErrorWriter::class);
        $injector->share($errorWriter);

        $statusWriter = new StdoutStatusWriter();
        $injector->share($statusWriter);
        $injector->alias(StatusWriter::class, get_class($statusWriter));

        $crawlerConfig = new CrawlerConfig(
            'http',
            $this->domainName,
            $this->initialPath
        );

        $injector->share($crawlerConfig);

        $rules = $injector->make(Rules::class);
        /** @var $siteChecker \SiteTool\SiteChecker */
        $siteChecker = $injector->make(SiteChecker::class);
        $responseParser = $injector->make(ResponseParser::class);
        $skippingAnnouncer = $injector->make(SkippingStatusWriter::class);

        $firstUrlToCheck = new URLToCheck('http://' . $this->domainName . $this->initialPath, '/');
        $promise = $siteChecker->followURL($firstUrlToCheck);

        $fn = function() {
            echo "I am unsure how what Amp function is best used here.";
        };

        //\Amp\wait($promise);
        \Amp\run($fn);

        $statusWriter->write("fin.");
    }
}
