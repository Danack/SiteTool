<?php

namespace SiteTool\Processor;

use SiteTool\Processor\Rules;
use SiteTool\SiteChecker;
use SiteTool\URLToCheck;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Amp\Artax\SocketException;
use FluentDOM\Document;
use FluentDOM\Element;
use SiteTool\Event\ParseEvent;
use SiteTool\Writer\OutputWriter;

class LinkFindingParser
{
    private $errors = 0;

    /** @var \Zend\EventManager\EventManager */
    private $eventManager;
    
    /** @var \SiteTool\Writer\OutputWriter  */
    private $outputWriter;
    
    public function __construct(
        OutputWriter $outputWriter//,
//        EventManager $eventManager,
//        Rules $rules,
//        $htmlReceivedEvent,
//        $foundUrlEvent
    ) {
//        $this->eventManager = $eventManager;
//        $eventManager->attach($htmlReceivedEvent, [$this, 'parseResponseEvent']);
//        $this->foundUrlEvent = $foundUrlEvent;
        
        $this->outputWriter = $outputWriter;
    }


    /**
     * @param URLToCheck $urlToCheck
     * @param $body
     */
    function parseResponse(
        URLToCheck $urlToCheck, $body,
        ParseEvent $parseEvent)
    {
        $ok = false;
        $path = $urlToCheck->getUrl();

        try {
            $document = new Document();
            $body = mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8');
            $document->loadHTML($body);
            $linkClosure = function (Element $element) use ($urlToCheck, $parseEvent) {
                $href = $element->getAttribute('href');
                $href = html_entity_decode($href);
                
                //$this->eventManager->trigger($this->foundUrlEvent, null, [$href, $urlToCheck->getUrl()]);
                $parseEvent->foundUrlEvent($href, $urlToCheck);
            };

            $document->find('//a')->each($linkClosure);
            $ok = true;
        }
        catch (SocketException $se) {
            $message = "Artax\\SocketException on $path - ".$se->getMessage(). " Exception type is ".get_class($se);
            $this->outputWriter->write(
                \SiteTool\Writer\OutputWriter::CRAWL_RESULT,
                $path,
                500,
                $urlToCheck->getReferrer(),
                $message
            );
        }
        catch(\InvalidArgumentException $iae) {
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
}
