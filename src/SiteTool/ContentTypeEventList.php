<?php


namespace SiteTool;

use Amp\Artax\Response;
use SiteTool\ErrorWriter;
use Zend\EventManager\EventManager;

class ContentTypeEventList
{
    /** @var EventManager */
    private $eventManager;
    
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @param Response $response
     * @param UrlToCheck $urlToCheck
     * @return null
     * @throws \Exception
     */
    public function triggerEventForContent(Response $response, UrlToCheck $urlToCheck)
    {
        $contentTypeHeaders = $response->getHeader('Content-Type');

        if (array_key_exists(0, $contentTypeHeaders) == false) {
            throw new \Exception("Content-type header not set.");
        }

        $contentType = $contentTypeHeaders[0];
        $colonPosition = strpos($contentType, ';');

        if ($colonPosition !== false) {
            $contentType = substr($contentType, 0, $colonPosition);
        }

        // echo "contentType is $contentType \n";

        switch ($contentType) {
            case ('text/html'): {
                $this->eventManager->trigger(SiteChecker::HTML_RECEIVED, null, [$urlToCheck, $response->getBody()]);
                break;
            }

            case ('text/plain'): {
                return null;
                break;
            }

            case ('application/octet-stream') :
            case ('image/gif') :
            case ('image/jpeg') :
            case ('image/jpg') :
            case ('image/vnd.adobe.photoshop') :
            case ('image/png') :
            case ('application/atom+xml') : {
                return null;
            }

            default: {
            // throw new \Exception("Unrecognised content-type $contentType");
               echo "Unrecognised content-type $contentType";
            }
        }
    }
}
