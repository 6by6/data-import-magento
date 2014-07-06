<?php

namespace Jh\DataImportTest\Reader;

use Jh\DataImportMagento\Reader\XmlReader;
use Symfony\Component\Config\Definition\Exception\Exception;

class XmlReaderTest extends \PHPUnit_Framework_TestCase
{
    protected $reader;

    public function setUp()
    {
    }

    public function testValidXmlCanBeParsedTrue()
    {
        $file = new \SplFileObject(__DIR__ . '/../Fixtures/valid_xml.xml');
        $this->reader = new XmlReader($file);
    }

    public function testInvalidXmlThrowsException()
    {
        $file = new \SplFileObject(__DIR__ . '/../Fixtures/invalid_xml.xml');

        $expectedMessage = "XML Parsing Failed. Errors: 'Premature end of data in tag orderStatus line 2'";

        $this->setExpectedException('Ddeboer\DataImport\Exception\ReaderException', $expectedMessage);
        $this->reader = new XmlReader($file);
    }

    public function testStructureOfDecodedXmlIsValid()
    {
        $file = new \SplFileObject(__DIR__ . '/../Fixtures/valid_xml.xml');
        $this->reader = new XmlReader($file, array(
            '//orderStatus/order',
            'lines/line'
        ));

        $expected = array(
            array(
                'clientCode'            => '54',
                'orderNumber'           => '000001',
                'customerOrderNumber'   => '000001',
                'userId'                => 'aydin',
                'userFullName'          => 'Aydin Hassan',
                'lineNumber'            => '1',
                'sku'                   => '4567',
                'qtyRequired'           => '1',
                'qtyAllocated'          => '1',
                'qtyDespatched'         => '0',
                'qtyCancelled'          => '0',
                'qtyLost'               => '0',
            ),
            array(
                'clientCode'            => '54',
                'orderNumber'           => '000001',
                'customerOrderNumber'   => '000001',
                'userId'                => 'aydin',
                'userFullName'          => 'Aydin Hassan',
                'lineNumber'            => '2',
                'sku'                   => '4568',
                'qtyRequired'           => '1',
                'qtyAllocated'          => '0',
                'qtyDespatched'         => '0',
                'qtyCancelled'          => '0',
                'qtyLost'               => '0',
            ),
        );

        $data = $this->reader->current();
        $this->assertEquals($expected[0], $data);
        $this->reader->next();
        $data = $this->reader->current();
        $this->assertEquals($expected[1], $data);
    }

    public function testGetFields()
    {
        $file = new \SplFileObject(__DIR__ . '/../Fixtures/valid_xml.xml');
        $this->reader = new XmlReader($file, array(
            '//orderStatus/order',
            'lines/line'
        ));

        $fields = array(
            'clientCode',
            'orderNumber',
            'customerOrderNumber',
            'userId',
            'userFullName',
            'lineNumber',
            'sku',
            'qtyRequired',
            'qtyAllocated',
            'qtyDespatched',
            'qtyCancelled',
            'qtyLost',
        );

        $this->assertEquals($fields, $this->reader->getFields());
    }

    public function testCount()
    {
        $file = new \SplFileObject(__DIR__ . '/../Fixtures/valid_xml.xml');
        $this->reader = new XmlReader($file, array(
            '//orderStatus/order',
            'lines/line'
        ));

        $this->assertSame(2, $this->reader->count());
    }
}
