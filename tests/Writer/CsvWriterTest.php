<?php

namespace SixBySix\PortTest\Writer;

use PHPUnit\Framework\TestCase;
use SixBySix\Port\Writer\CsvWriter;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class CsvWriterTest extends TestCase
{
    private $tempFile;

    protected function setUp()
    {
        $this->tempFile = sprintf('%s/%s.csv', sys_get_temp_dir(), uniqid($this->getName(), true));
    }

    protected function tearDown()
    {
        unlink($this->tempFile);
    }

    public function testEmptyFieldsAreNotWrappedInEnclosureWhenOptionIsSetToFalse()
    {
        $writer = new CsvWriter(new \SplFileObject($this->tempFile, 'w+'), 'w+', ',', '"', "\n", true);
        $writer->writeItem(['one', 'two', '', 'four']);
        $writer->finish();

        $this->assertSame("\"one\",\"two\",\"\",\"four\"\n", file_get_contents($this->tempFile));
    }

    public function testEmptyFieldsAreNotWrappedInEnclosureByDefault()
    {
        $writer = new CsvWriter(new \SplFileObject($this->tempFile, 'w+'), 'w+', ',', '"', "\n");
        $writer->writeItem(['one', 'two', '', 'four']);
        $writer->finish();

        $this->assertSame("\"one\",\"two\",,\"four\"\n", file_get_contents($this->tempFile));
    }
}
