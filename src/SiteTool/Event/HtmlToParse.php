<?php

declare(strict_types=1);

namespace SiteTool\Event;

use SiteTool\UrlToCheck;
use Amp\Artax\Response;

class HtmlToParse
{
    /** @var Response  */
    private $response;
    
    /** @var UrlToCheck  */
    private $urlToCheck;

    /** @var string  */
    private $responseBody;

    public function __construct(UrlToCheck $urlToCheck, Response $response, string $responseBody)
    {
        $this->response = $response;
        $this->urlToCheck = $urlToCheck;
        $this->responseBody = $responseBody;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return UrlToCheck
     */
    public function getUrlToCheck(): UrlToCheck
    {
        return $this->urlToCheck;
    }

    /**
     * @return string
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
