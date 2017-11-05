<?php

declare(strict_types=1);

namespace SiteTool\Processor\Data;

use SiteTool\UrlToCheck;

class HtmlIsInvalid
{
    /** @var  \SiteTool\UrlToCheck */
    private $urlToCheck;

    /** @var array  */
    private $htmlErrors;

    /**
     * HtmlValidationResult constructor.
     * @param \SiteTool\W3CValidator $validator
     * @param \SiteTool\UrlToCheck $urlToCheck
     * @param string $fullURL
     */
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
