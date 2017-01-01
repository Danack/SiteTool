<?php

namespace SiteTool\Writer;

class WriterFactory
{
    private $fileWritersByPath = [];
    
    function createFileWriter($filename)
    {
        if (array_key_exists($filename, $this->fileWritersByPath) === true) {
            return $this->fileWritersByPath[$filename];
        }
        $fileWriter = new \SiteTool\Writer\FileWriter($filename);
        $fileWritersByPath[$filename] = $fileWriter;
        
        return $fileWriter;
    }
    
    function create($outputTypeOrFilename)
    {
        switch ($outputTypeOrFilename) {
            case null:
            case 'null':
                return new \SiteTool\Writer\NullWriter();
            case 'stdout':
                return new \SiteTool\Writer\StdoutWriter();
            case 'stderr':
                return new \SiteTool\Writer\StderrWriter();
        }
    
        return $this->createFileWriter($outputTypeOrFilename);
    }
}
