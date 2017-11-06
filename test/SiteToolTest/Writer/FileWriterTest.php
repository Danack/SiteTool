<?php


namespace SiteToolTest\Writer;

use SiteTool\SiteToolException;
use function Amp\File\open;
use function Amp\resolve;
use SiteTool\Writer\FileWriter;
use SiteToolTest\BaseTestCase;

class FileWriterTest extends BaseTestCase
{
    const FILENAME = TEMP_PATH . "/output.txt";

    public function tearDown()
    {
        parent::tearDown();

        @unlink(self::FILENAME);

        $this->assertFalse(file_exists(self::FILENAME));
    }

    public function testThrowsOnEmptyFilename()
    {
        $this->setExpectedException(\Exception::class, "Filename cannot be empty.");

        new FileWriter('');
    }

    public function testWriteCreatesFile()
    {
        (new FileWriter(self::FILENAME))->write("");

        $this->assertTrue(file_exists(self::FILENAME));
    }

    public function testWriteWithoutOtherStrings()
    {
        (new FileWriter(self::FILENAME))->write("foo");

        $this->assertSame("foo\n", file_get_contents(self::FILENAME));
    }

    public function testWriteCombinesOtherStrings()
    {
        (new FileWriter(self::FILENAME))->write("foo", "bar", "baz");

        $this->assertSame("foo, bar, baz\n", file_get_contents(self::FILENAME));
    }

    public function testWriteAppends()
    {
        $writer = new FileWriter(self::FILENAME);

        $writer->write("foo");
        $writer->write("bar");

        $this->assertSame("foo\nbar\n", file_get_contents(self::FILENAME));
    }
}
