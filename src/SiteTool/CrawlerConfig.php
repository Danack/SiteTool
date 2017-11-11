<?php


namespace SiteTool;

class CrawlerConfig
{
    public $schema = 'http';
    public $domainName;
    public $path = '/';

    public function __construct($schema, $domainName, $path)
    {
        $this->schema = $schema;
        $this->domainName = $domainName;
        $this->path = $path;
    }
    
    public function getPath($path)
    {
        return sprintf(
            "%s://%s%s",
            $this->schema,
            $this->domainName,
            $path
        );
    }
}
