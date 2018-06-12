<?php

namespace SiteTool\Printer;

use SiteTool\Result;

class HTMLPrinter
{
    /**
     * @var Result[]
     */
    private $results;

    /** @var string */
    private $baseURL;
    
    public function __construct(array $results, string $baseURL)
    {
        $this->results = $results;
        $this->baseURL = $baseURL;
    }

    public function output($outputStream)
    {
        fwrite($outputStream, "<html>");
        fwrite($outputStream, "<body>");
        fwrite($outputStream, "<table>");
        fwrite($outputStream, "<thead>");
        fwrite($outputStream, "<tr>");
        fwrite($outputStream, "<th>Status</th>");
        fwrite($outputStream, "<th>Path</th>");
        fwrite($outputStream, "<th>Referrer</th>");
        fwrite($outputStream, "<th>Message</th>");
        
        fwrite($outputStream, "</tr>");
        fwrite($outputStream, "</thead>");
        
        fwrite($outputStream, "<tbody>");

        foreach ($this->results as $path => $result) {
            if ($result) {
                if ($result->getStatus() != 200) {
                    fwrite($outputStream, "<tr>");
        
                    fwrite($outputStream, "<td>");
                    fwrite($outputStream, "" . $result->getStatus());
    
                    fwrite($outputStream, "</td>");
    
                    fwrite($outputStream, "<td>");
                    fwrite($outputStream, sprintf("<a href='%s%s'>", $this->baseURL, $result->getPath()));
                    fwrite($outputStream, "" . $result->getPath());
                    fwrite($outputStream, "</a>");
                    fwrite($outputStream, "</td>");

                    fwrite($outputStream, "<td>");
                    fwrite($outputStream, "" . $result->getReferrer());
                    fwrite($outputStream, "</td>");
                    
                    fwrite($outputStream, "<td>");
                    fwrite($outputStream, "" . $result->getErrorMessage());
                    fwrite($outputStream, "</td>");
        
                    fwrite($outputStream, "</tr>");
                }
            }
        }

        fwrite($outputStream, "</tbody>");

        fwrite($outputStream, "</table>");
        fprintf($outputStream, "<span>There were %d URLs scanned succesfully.</span>", count($this->results));
        fwrite($outputStream, "</body>");
        fwrite($outputStream, "</html>");
    }
}
