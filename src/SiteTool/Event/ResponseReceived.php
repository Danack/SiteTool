<?php

declare(strict_types=1);

namespace SiteTool\Event;

use Amp\Artax\Response;
use SiteTool\UrlToCheck;

class ResponseReceived
{
    /** @var response  */
    private $response;

    /** @var string  */
    private $responseBody;

    /** @var UrlToCheck  */
    private $urlToCheck;
    
    public function __construct(Response $response, string $responseBody, UrlToCheck $urlToCheck)
    {
        $this->response = $response;
        $this->responseBody = $responseBody;
        $this->urlToCheck = $urlToCheck;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * @return UrlToCheck
     */
    public function getUrlToCheck(): UrlToCheck
    {
        return $this->urlToCheck;
    }
}
