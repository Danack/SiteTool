<?php


namespace SiteTool\Writer;

use Amp\File\Handle;
use SiteTool\SiteToolException;
use SiteTool\Writer;
use function Amp\File\open;
use function Amp\resolve;

class FileWriter implements Writer
{
    /** @var null|Handle */
    private $fileHandle = null;
    private $filename;

    public function __construct($filename)
    {
        if (strlen($filename) == 0) {
            throw new \Exception("Filename cannot be empty.");
        }
        $this->filename = $filename;
    }

    private function init()
    {
        resolve(function() {
            if ($this->fileHandle) {
                return;
            }

            $this->fileHandle = yield open($this->filename, "w");
            if ($this->fileHandle === false) {
                throw new SiteToolException("Failed to open " . $this->filename . " for writing.");
            }
        });
    }

    public function __destruct()
    {
        resolve(function() {
            if ($this->fileHandle) {
                yield $this->fileHandle->close();
            }
        });
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

        resolve(function() use ($line) {
            yield $this->fileHandle->write($line);
        });
    }
}
