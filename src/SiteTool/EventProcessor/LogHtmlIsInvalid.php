<?php

namespace SiteTool\EventProcessor;

use SiteTool\UrlToCheck;
use SiteTool\EventManager;
use SiteTool\Writer\OutputWriter;
use SiteTool\Event\HtmlIsInvalid;

class LogHtmlIsInvalid
{
    private $switchName = "Output validation\nis invalid result";

    /** @var OutputWriter */
    private $outputWriter;

    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $this->outputWriter = $outputWriter;
        $eventManager->attachEvent(HtmlIsInvalid::class, [$this, 'processResult'], $this->switchName);
    }

    /**
     * @param URLToCheck $urlToCheck
     */
    public function processResult(HtmlIsInvalid $foundUrlToFollow)
    {
        printf("Validation errors for url %s \n", $foundUrlToFollow->getUrlToCheck()->getUrl());
        foreach ($foundUrlToFollow->getHtmlErrors() as $error) {
            echo $error . "\n";
        }
    }
}
