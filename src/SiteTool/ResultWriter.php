<?php


namespace SiteTool;


interface ResultWriter
{
    public function write(
        $url,
        $status,
        $referrer,
        $body
    );
}
