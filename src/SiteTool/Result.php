<?php


namespace SiteTool;

class Result
{
    public $status;
    public $url;
    public $referrer;

    function __construct($url, $status, $referrer)
    {
        $this->url = $url;
        $this->status = $status;
        $this->referrer = $referrer;
    }
}
