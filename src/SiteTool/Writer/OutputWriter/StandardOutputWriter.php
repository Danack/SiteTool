<?php


namespace SiteTool\Writer\OutputWriter;

use SiteTool\SiteToolException;

use SiteTool\Writer\CrawlResultWriter;
use SiteTool\Writer\ErrorWriter;
use SiteTool\Writer\MigrationResultWriter;
use SiteTool\Writer\OutputWriter;
use SiteTool\Writer\StatusWriter;


class StandardOutputWriter implements OutputWriter
{
    /** @var \SiteTool\Writer[] */
    private $writers = [];

    public function __construct(
        CrawlResultWriter $crawlResultWriter,
        StatusWriter $statusWriter,
        ErrorWriter $errorWriter,
        MigrationResultWriter $migrationResultWriter
    ) {
        $this->writers[OutputWriter::PROGRESS] = $statusWriter;
        $this->writers[OutputWriter::CRAWL_RESULT] = $crawlResultWriter;
        $this->writers[OutputWriter::ERROR] = $errorWriter;
        $this->writers[OutputWriter::MIGRATION_RESULT] = $migrationResultWriter;
    }

    public function write($type, $string, ...$otherStrings)
    {
        $originalType = $type;
        
        $knownWriters = [
            OutputWriter::PROGRESS,
            OutputWriter::CRAWL_RESULT, 
            OutputWriter::ERROR,
            OutputWriter::MIGRATION_RESULT
        ];
        
        foreach ($knownWriters as $knownWriter) {
            if (($type & $knownWriter) !== 0) {
                $this->writers[$knownWriter]->write($string, ...$otherStrings);
            }

            $type = ($type & (~$knownWriter));
        }
        
        if ($type !== 0) {
            throw new \Exception("Unknown output type in $originalType");
        }
    }
}
