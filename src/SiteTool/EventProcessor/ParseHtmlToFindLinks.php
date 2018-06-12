<?php

namespace SiteTool\EventProcessor;

use SiteTool\UrlToCheck;
use SiteTool\Event\FoundUrl;
use SiteTool\EventManager;
use SiteTool\Event\HtmlToParse;
use Amp\Artax\SocketException;
use FluentDOM\Document;
use FluentDOM\Element;
use SiteTool\Writer\OutputWriter;

class ParseHtmlToFindLinks implements Relay
{
    private $errors = 0;
    
    /** @var callable */
    private $foundUrlEventTrigger;

    private $switchName = "Parse the HTML to find links";

    /** @var \SiteTool\Writer\OutputWriter  */
    private $outputWriter;
    
    public function __construct(
        EventManager $eventManager,
        OutputWriter $outputWriter
    ) {
        $this->outputWriter = $outputWriter;
        $eventManager->attachEvent(HtmlToParse::class, [$this, 'parseResponse'], $this->switchName);
        $this->foundUrlEventTrigger = $eventManager->createTrigger(FoundUrl::class, $this->switchName);
    }

    /**
     * @param URLToCheck $urlToCheck
     * @param $body
     */
    public function parseResponse(HtmlToParse $htmlToParse)
    {
        $urlToCheck = $htmlToParse->getUrlToCheck();
        $body = $htmlToParse->getResponseBody();
        
        $ok = false;
        $path = $urlToCheck->getUrl();

        try {
            $document = new Document();
            $body = mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8');
            $document->loadHTML($body);
            $linkClosure = function (Element $element) use ($urlToCheck) {
                $href = $element->getAttribute('href');
                $href = html_entity_decode($href);
                $fn = $this->foundUrlEventTrigger;
                $fn(new FoundUrl($href, $urlToCheck));
            };

            $document->find('//a')->each($linkClosure);
            $ok = true;
        }
//        catch (SocketException $se) {
//            $message = "Artax\\SocketException on $path - ".$se->getMessage(). " Exception type is ".get_class($se);
//            $this->outputWriter->write(
//                \SiteTool\Writer\OutputWriter::CRAWL_RESULT,
//                $path,
//                500,
//                $urlToCheck->getReferrer(),
//                $message
//            );
//        }
        catch (\InvalidArgumentException $iae) {
            $message = "Fluent dom exception on $path - ".$iae->getMessage(). " Exception type is ".get_class($iae);
            $this->outputWriter->write(
                \SiteTool\Writer\OutputWriter::CRAWL_RESULT,
                $path,
                500,
                $urlToCheck->getReferrer(),
                $message
            );
        }

        if ($ok != true) {
            $this->errors++;
        }
    }

    public function getAsyncWorkers()
    {
        return [];
    }
}
