<?php


namespace SiteTool;

class AppConfig
{
    public $crawlOutput;
    public $migrationOutput;
    public $checkOutput;
    public $statusOutput;
    public $errorOutput;

    function __construct($crawlOutput = null, $migrationOutput = null, $checkOutput = null, $statusOutput = null, $errorOutput = null)
    {
        $this->crawlOutput = $crawlOutput;
        $this->migrationOutput = $migrationOutput;
        $this->checkOutput = $checkOutput;
        $this->statusOutput = $statusOutput;
        $this->errorOutput = $errorOutput;
    }
}