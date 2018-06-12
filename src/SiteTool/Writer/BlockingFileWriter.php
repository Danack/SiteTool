<?php


namespace SiteTool\Writer;

use SiteTool\Writer;

class BlockingFileWriter implements Writer
{
    /** @var resource|null */
    private $fileHandle = null;
    private $filename;

    /**
     * FileWriter constructor.
     * @param string $filename
     * @throws \Exception
     */
    public function __construct(string $filename)
    {
        if (strlen($filename) == 0) {
            throw new \Exception("Filename cannot be empty.");
        }
        $this->filename = $filename;
    }

    private function init()
    {
        $this->fileHandle = fopen($this->filename, "w");
    }

    public function __destruct()
    {
        if ($this->fileHandle !== null) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    public function write($string, ...$otherStrings)
    {
        $this->init();
        $line = $string;
        if (count($otherStrings) !== 0) {
            $line .= ", ";
        }

        $line .= implode(", ", $otherStrings);
        $line .= "\n";

        fwrite($this->fileHandle, $line);
    }
}
