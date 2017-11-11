<?php

namespace SiteTool\Writer\OutputWriter;

use SiteTool\Writer\OutputWriter;
use SiteTool\AppConfig;
use SiteTool\Writer\WriterFactory;

class StandardOutputWriter implements OutputWriter
{
    /** @var \SiteTool\Writer[] */
    private $writers = [];

    public function __construct(
        AppConfig $appConfig,
        WriterFactory $writerFactory
    ) {
        $this->writers[OutputWriter::PROGRESS] = $writerFactory->create($appConfig->statusOutput);
        $this->writers[OutputWriter::CRAWL_RESULT] = $writerFactory->create($appConfig->crawlOutput);
        $this->writers[OutputWriter::ERROR] = $writerFactory->create($appConfig->errorOutput);
        $this->writers[OutputWriter::MIGRATION_RESULT] = $writerFactory->create($appConfig->migrationOutput);
        $this->writers[OutputWriter::CHECK_RESULT] = $writerFactory->create($appConfig->checkOutput);
    }

    public function write($type, $string, ...$otherStrings)
    {
        $originalType = $type;
        
        $knownWriters = [
            OutputWriter::PROGRESS,
            OutputWriter::CRAWL_RESULT,
            OutputWriter::ERROR,
            OutputWriter::MIGRATION_RESULT,
            OutputWriter::CHECK_RESULT
        ];
        
        foreach ($knownWriters as $knownWriter) {
            if (($type & $knownWriter) !== 0) {
                $writer = $this->writers[$knownWriter];
                if ($writer === null) {
                    throw new \Exception("Writer type [$type] is not configured for this tool.");
                }
                $writer->write($string, ...$otherStrings);
            }

            $type = ($type & (~$knownWriter));
        }
        
        if ($type !== 0) {
            throw new \Exception("Unknown output type in $originalType");
        }
    }
}
