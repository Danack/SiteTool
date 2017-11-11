<?php

namespace SiteTool;

class UrlToCheck
{
    private $url;
    private $referrer;
    
    public function __construct($url, $referrer)
    {
        $this->url = $url;
        $this->referrer = $referrer;
    }

    /**
     * @return mixed
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }
}
