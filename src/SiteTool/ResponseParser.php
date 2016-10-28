<?php

namespace SiteTool;

use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use SiteTool\SiteChecker;

use Amp\Artax\Client as ArtaxClient;
use Amp\Artax\SocketException;
use Amp\Artax\Response;
use FluentDOM\Document;
use FluentDOM\Element;
use SiteTool\ResultWriter\FileResultWriter;
use SiteTool\ErrorWriter;



class ResponseParser
{

    public function __construct(EventManager $eventManager)
    {
        $eventManager->attach(SiteChecker::HTML_RECEIVED, [$this, 'parseResponse']);    
    }
    
    public function parseResponse(Event $e)
    {

            $event  = $e->getName();
            $target = get_class($e->getTarget());
            $params = json_encode($e->getParams());

//            $log->info(sprintf(
//                '%s called on %s, using params %s',
//                $event,
//                $target,
//                $params
//            ));
    }


    /**
     * @param URLToCheck $urlToCheck
     * @param $body
     */
    function analyzeHtmlBody(URLToCheck $urlToCheck, $body)
    {
        $ok = false;
        $path = $urlToCheck->getUrl();

        try {
            $document = new Document();
            $body = mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8');
            $document->loadHTML($body);
            $linkClosure = function (Element $element) use ($urlToCheck) {
                $this->parseLinkResult($element, $urlToCheck->getUrl());
            };
//            $imgClosure = function (Element $element) use ($urlToCheck) {
//                $this->parseImgResult($element, $urlToCheck->getUrl());
//            };

            $document->find('//a')->each($linkClosure);
            //$document->find('//img')->each($imgClosure);
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
        catch(\Exception $e) {
            $message = "Error getting $path - " . $e->getMessage() . " Exception type is " . get_class($e);
            $message .= $e->getTraceAsString();

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
