<?php

namespace SiteTool\EventProcessor;

use SiteTool\UrlToCheck;
use SiteTool\EventManager;
use SiteTool\Event\ResponseOk;
use SiteTool\Event\ResponseIsValid;
use SiteTool\Event\ResponseIsInvalid;
use SiteTool\Event\EndOfProcessing;

class ValidateApiResponse implements Relay
{
    /** @var  callable */
    private $responseIsValidTrigger;

    /** @var  callable */
    private $responseIsInvalidTrigger;

    private $switchName = "Validate Api Response";

    private $validItems = 0;

    public function __construct(EventManager $eventManager)
    {
        $eventManager->attachEvent(ResponseOk::class, [$this, 'validateApiResponse'], $this->switchName);
        $eventManager->attachEvent(EndOfProcessing::class, [$this, 'endOfProcessing'], $this->switchName);

        $this->responseIsValidTrigger = $eventManager->createTrigger(ResponseIsValid::class, $this->switchName);
        $this->responseIsInvalidTrigger = $eventManager->createTrigger(ResponseIsInvalid::class, $this->switchName);
    }

    public function validateApiResponse(ResponseOk $responseOk)
    {
        $json = $responseOk->getResponseReceived()->getResponseBody();

        try {
            $data = json_decode($json, true);
            ($this->responseIsValidTrigger)(new ResponseIsValid($data, $responseOk->urlToCheck));
            $this->validItems += 1;
        }
        catch (\Exception $e) {
            ($this->responseIsInvalidTrigger)(new ResponseIsInvalid($e->getMessage(), $responseOk->urlToCheck));
        }
    }

    public function getAsyncWorkers()
    {
        return [];
    }

    public function endOfProcessing()
    {
        printf(
            "We scanned %s items.\n",
            $this->validItems
        );
    }
}
