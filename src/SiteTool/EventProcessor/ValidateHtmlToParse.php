<?php

namespace SiteTool\EventProcessor;

use SiteTool\UrlToCheck;
use SiteTool\EventManager;
use Amp\Artax\Client as ArtaxClient;
use SiteTool\Writer\OutputWriter;
use SiteTool\Event\HtmlIsValid;
use SiteTool\Event\HtmlIsInvalid;
use SiteTool\Event\HtmlToParse;

class ValidateHtmlToParse implements Relay
{
    /** @var  callable */
    private $htmlIsValidTrigger;

    /** @var  callable */
    private $htmlIsInvalidTrigger;

    private $switchName = "Validate HtmlToParse";


    /**
     * @var ArtaxClient
     */
    private $artaxClient;
    
    /** @var OutputWriter */
    private $outputWriter;

    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter,
        ArtaxClient $artaxClient
    ) {
        $this->outputWriter = $outputWriter;
        $this->artaxClient = $artaxClient;
        $eventManager->attachEvent(HtmlToParse::class, [$this, 'validateHtml'], $this->switchName);
        $this->htmlIsValidTrigger = $eventManager->createTrigger(HtmlIsValid::class, $this->switchName);
        $this->htmlIsInvalidTrigger = $eventManager->createTrigger(HtmlIsInvalid::class, $this->switchName);
    }

    public function validateHtml(HtmlToParse $htmlToParse)
    {
        $html = $htmlToParse->getResponseBody();

        $tmpfname = tempnam("./var/tmp", "tidycheck_");
        file_put_contents($tmpfname, $html);
        $output = [];
        $return_var = 0;
        exec("tidy -e -q " . $tmpfname . " 2>&1", $output, $return_var);
        unlink($tmpfname);

        if ($return_var !== 0) {
            ($this->htmlIsInvalidTrigger)(new HtmlIsInvalid($htmlToParse->getUrlToCheck(), $output));
        }
        else {
            ($this->htmlIsValidTrigger)(new HtmlIsValid($htmlToParse->getUrlToCheck()));
        }
    }

    public function getAsyncWorkers()
    {
        return [];
    }
}
