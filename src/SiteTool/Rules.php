<?php


namespace SiteTool;

use SiteTool\StatusWriter;

class Rules
{
    private $crawlerConfig;

    private $statusWriter;
    
    public function __construct(
        CrawlerConfig $crawlerConfig,
        StatusWriter $statusWriter)
    {
        $this->crawlerConfig = $crawlerConfig;
        $this->statusWriter = $statusWriter;
    }

    /**
     * @param $href
     * @param $referrer
     * @return null|UrlToCheck
     */
    public function getUrlToCheck($href, $referrer)
    {
        

        $knownNonLinks = [
            'mailto',
            'javascript',
        ];
        
        foreach ($knownNonLinks as $knownNonLink) {
            if (stripos($href, $knownNonLink) === 0) {
                /// $this->statusWriter->write("skipping known non-link $knownNonLink");
                return null;
            }
        }
        
        if (strpos($href, '//') === 0) {
            $href = sprintf("%s://%s",
                $this->crawlerConfig->schema, // todo, should be schema of referer
                substr($href, 2)
            );
        }

        
        $parsedUrl = parse_url($href);

        if (array_key_exists('host', $parsedUrl) === true) {
            // If it points to a different domain, don't follow.
            if (stripos($parsedUrl['host'], $this->crawlerConfig->domainName) === false) {
                // $this->statusWriter->write("Skipping $href as host " . $parsedUrl['host'] . " is different.");
                return null;
            }
            // $this->statusWriter->write("Following absolute URL $href");
            // If it points to same domain, follow.
            return new UrlToCheck($href, $referrer);
        }

        //$this->statusWriter->write("Following relative path $href");
        
        //It's relative
        return new UrlToCheck(
            $this->crawlerConfig->getPath($href),
            $referrer
        );
    }
    
    
//    PHP_URL_SCHEME, PHP_URL_HOST, PHP_URL_PORT, PHP_URL_USER, PHP_URL_PASS, PHP_URL_PATH, PHP_URL_QUERY or PHP_URL_FRAGMENT to retrieve just a specific URL component as a string (except when PHP_URL_PORT
    
    
    public function shouldFollow($fullURL)
    {
        if (strpos($fullURL, '/queueinfo') !== false) {
            return false;
        }

        return true;
    }
    
    public function shouldFollowURLToCheck(URLToCheck $urlToCheck)
    {
        echo " url is ". $urlToCheck->getUrl();
        
        exit(0);
    }
}
        
