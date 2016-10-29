<?php

namespace SiteTool;

use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Amp\Artax\SocketException;
use FluentDOM\Document;
use FluentDOM\Element;
use SiteTool\ErrorWriter;

class ResponseParser
{
    private $errors = 0;

    /** @var \Zend\EventManager\EventManager */
    private $eventManager;
    
    /** @var \SiteTool\ResultWriter  */
    private $resultWriter;
    
    public function __construct(
        EventManager $eventManager,
        Rules $rules,
        ResultWriter $resultWriter
    ) {
        $this->rules = $rules;
        $this->eventManager = $eventManager;
        $this->resultWriter = $resultWriter;
        $eventManager->attach(SiteChecker::HTML_RECEIVED, [$this, 'parseResponseEvent']);    
    }

    /**
     * @param Event $e
     */
    public function parseResponseEvent(Event $e)
    {
        $params = $e->getParams();
        $urlToCheck = $params[0];
        $responseBody = $params[1];

        $this->parseResponse($urlToCheck, $responseBody);
    }

    /**
     * @param URLToCheck $urlToCheck
     * @param $body
     */
    function parseResponse(URLToCheck $urlToCheck, $body)
    {
        $ok = false;
        $path = $urlToCheck->getUrl();
        // echo "Parsing body for " . $urlToCheck->getUrl() . "\n";

        try {
            $document = new Document();
            $body = mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8');
            $document->loadHTML($body);
            $linkClosure = function (Element $element) use ($urlToCheck) {
                $href = $element->getAttribute('href');
                $this->eventManager->trigger(SiteChecker::FOUND_URL, null, [$href, $urlToCheck->getUrl()]);
            };

            $document->find('//a')->each($linkClosure);
            $ok = true;
        }
        catch (SocketException $se) {
            $message = "Artax\\SocketException on $path - ".$se->getMessage(). " Exception type is ".get_class($se);
            $this->resultWriter->write(
                $path,
                500,
                $urlToCheck->getReferrer(),
                $message
            );
        }
        catch(\InvalidArgumentException $iae) {
            $message = "Fluent dom exception on $path - ".$iae->getMessage(). " Exception type is ".get_class($iae);
            $this->resultWriter->write(
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
