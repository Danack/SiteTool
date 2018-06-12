<?php

declare(strict_types=1);

namespace SiteTool\Event;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;
use SiteTool\Event\ResponseReceived;

class ResponseIsValid
{
    /** @var array */
    public $data;

    /** @var UrlToCheck */
    public $urlToCheck;
    
    public function __construct(array $data, UrlToCheck $urlToCheck)
    {
        $this->data = $data;
        $this->urlToCheck = $urlToCheck;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return UrlToCheck
     */
    public function getUrlToCheck(): UrlToCheck
    {
        return $this->urlToCheck;
    }
}
