<?php

declare(strict_types=1);

namespace SiteTool\Event;

use SiteTool\UrlToCheck;

class HtmlIsInvalid
{
    /** @var  \SiteTool\UrlToCheck */
    private $urlToCheck;

    /** @var array  */
    private $htmlErrors;

    public function __construct(UrlToCheck $urlToCheck, array $htmlErrors)
    {
        $this->urlToCheck = $urlToCheck;
        $this->htmlErrors = $htmlErrors;
    }

    /**
     * @return UrlToCheck
     */
    public function getUrlToCheck(): UrlToCheck
    {
        return $this->urlToCheck;
    }

    /**
     * @return array
     */
    public function getHtmlErrors(): array
    {
        return $this->htmlErrors;
    }
}
