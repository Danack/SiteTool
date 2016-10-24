<?php


namespace SiteTool;

class Rules
{
    private $crawlerConfig;
    
    public function __construct(CrawlerConfig $crawlerConfig)
    {
        $this->crawlerConfig = $crawlerConfig;
    }

    /**
     * @param $href
     * @param $referrer
     * @return null|UrlToCheck
     */
    public function getUrlToCheck($href, $referrer)
    {
        $parsedUrl = parse_url($href);

        $knownNonLinks = [
            'mailto',
            'javascript',
        ];
        
        foreach ($knownNonLinks as $knownNonLink) {
            if (stripos($href, $knownNonLink) === 0) {
                return null;
            }
        }
    
        
        if (array_key_exists('host', $parsedUrl) === true) {
            // If it points to a different domain, don't follow.
            if (stripos($parsedUrl['host'], $this->crawlerConfig->domainName) === false) {
//                echo "here";
//                exit(0);
                return null;
            }

//            echo "here 2 $href";
//                exit(0);
            // If it points to same domain, follow.
            return new UrlToCheck($href, $referrer);
            
        }


        
        if (strpos($href, '//') === 0) {
            $url = sprintf("%s://%s",
                $this->crawlerConfig->schema,
                substr($href, 2)
            );
            return new UrlToCheck(
                $url,
                $referrer
            );
        }
        
        
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
        
