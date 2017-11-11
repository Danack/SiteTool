<?php

namespace SiteToolTest;

use SiteTool\EventProcessor\Rules;
use SiteTool\ErrorWriter\NullErrorWriter;
use SiteTool\StatusWriter\NullStatusWriter;
use SiteTool\ErrorWriter\EchoErrorWriter;
use SiteTool\CrawlerConfig;

class RulesTest // extends \PHPUnit_Framework_TestCase 
    extends \SiteToolTest\BaseTestCase
{
    
    private function createRules($domainName)
    {
        $crawlerConfig = new CrawlerConfig(
            'http',
            $domainName,
            '/'
        );
        return new Rules(
            $crawlerConfig,
            new NullStatusWriter(),
            new NullErrorWriter()
        );
    }

    /**
     * @group debug
     */
    public function testWat() 
    {
        $href = "http://www.example.com/foo/bar/quux";
        $rules = $this->createRules("example.com");
        $urlToCheck = $rules->getUrlToCheck($href, '/');
        $this->assertNotNull($urlToCheck);
    }

    public function doNotFollowLinks()
    {
        return [
            ['www.example.com', "https://www.facebook.com/sciencefocus"],
            ['www.example.com', "sms:012345"],
            ['www.example.com', "skype:012345"],
            ['www.example.com', "http://www.example.comfoo.bar"],
        ];
    }

    public function doFollowLinks()
    {
        return [
            ["example.com", "//example.com/just-in/foo.html"],
        ];
    }

    /**
     * @dataProvider doFollowLinks
     */
    public function testSchemaPath($domainName, $href)
    {
        $rules = $this->createRules($domainName);
        $urlToCheck = $rules->getUrlToCheck($href, '/');
        $this->assertNotNull($urlToCheck);
        $this->assertEquals("http://$domainName/just-in/foo.html", $urlToCheck->getUrl());
    }
    

    /**
     * @dataProvider doNotFollowLinks
     */
    public function testDontFollowLinkToOtherSite($domainName, $href)
    {
        $rules = $this->createRules($domainName);

        $urlToCheck = $rules->getUrlToCheck($href, '/');
        $this->assertNull($urlToCheck);
    }
    
}


