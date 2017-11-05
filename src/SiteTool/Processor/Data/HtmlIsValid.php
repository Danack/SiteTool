<?php

declare(strict_types=1);

namespace SiteTool\Processor\Data;

use SiteTool\W3CValidator;
use SiteTool\UrlToCheck;

class HtmlIsValid
{
    /** @var  \SiteTool\UrlToCheck */
    private $urlToCheck;

    /** @var string */
    private $fullURL;

    /**
     * HtmlValidationResult constructor.
     * @param \SiteTool\UrlToCheck $urlToCheck
     */
    public function __construct(UrlToCheck $urlToCheck)
    {
        $this->urlToCheck = $urlToCheck;
    }

    /**
     * @return UrlToCheck
     */
    public function getUrlToCheck(): UrlToCheck
    {
        return $this->urlToCheck;
    }
}
