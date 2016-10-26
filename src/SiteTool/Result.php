<?php


namespace SiteTool;

class Result
{
    public $status;
    public $url;
    public $referrer;

    function __construct($status, $url, $referrer)
    {
        $this->status = $status;
        $this->url = $url;
        $this->referrer = $referrer;
    }
}
