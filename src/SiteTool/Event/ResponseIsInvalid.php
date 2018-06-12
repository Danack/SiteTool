<?php

declare(strict_types=1);

namespace SiteTool\Event;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;
use SiteTool\Event\ResponseReceived;

class ResponseIsInvalid
{
    /** @var string */
    public $error;

    /** @var UrlToCheck */
    public $urlToCheck;
    
    public function __construct(string $error, UrlToCheck $urlToCheck)
    {
        $this->error = $error;
        $this->urlToCheck = $urlToCheck;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @return UrlToCheck
     */
    public function getUrlToCheck(): UrlToCheck
    {
        return $this->urlToCheck;
    }
}
