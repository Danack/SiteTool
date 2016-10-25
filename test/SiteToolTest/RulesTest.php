<?php

namespace SiteToolTest;

use SiteTool\Rules;
use SiteTool\StatusWriter\NullStatusWriter;
use SiteTool\CrawlerConfig;

class RulesTest extends \SiteToolTest\BaseTestCase
{
    public function testSchemaPath()
    {
        $crawlerConfig = new CrawlerConfig(
            'http',
            'example.com',
            '/'
        );
        $rules = new Rules(
            $crawlerConfig,
            new NullStatusWriter()
        );
     
        $urlToCheck = $rules->getUrlToCheck("//example.com/just-in/foo.html", '/');
        $this->assertNotNull($urlToCheck);
        $this->assertEquals('http://example.com/just-in/foo.html', $urlToCheck->getUrl());
    }
}


