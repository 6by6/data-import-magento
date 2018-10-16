<?php

namespace SixBySix\Port\Reader;

/**
 * @internal
 * @coversNothing
 */
final class XmlTest extends \PHPUnit\Framework\TestCase
{
    protected $reader;

    protected function setUp()
    {
    }

    public function testValidXmlCanBeParsedTrue()
    {
        $file = fopen(__DIR__.'/../Fixtures/valid_xml.xml', 'rb');
        $this->reader = new Xml($file);

        $this->assertInstanceOf(Xml::class, $this->reader);
    }

    /**
     * @expectedException \Port\Exception\ReaderException
     * @expectedExceptionMessage XML Parsing Failed. Errors: 'Premature end of data in tag orderStatus line 2'
     */
    public function testInvalidXmlThrowsException()
    {
        $file = fopen(__DIR__.'/../Fixtures/invalid_xml.xml', 'r+b');

        $this->reader = new Xml($file);
    }

    public function testStructureOfDecodedXmlIsValid()
    {
        $file = fopen(__DIR__.'/../Fixtures/valid_xml.xml', 'r+b');
        $this->reader = new Xml(
            $file,
            [
                '//orderStatus/order',
                'lines/line',
            ],
            'merge'
        );

        $expected = [
            [
                'clientCode' => '54',
                'orderNumber' => '000001',
                'customerOrderNumber' => '000001',
                'userId' => 'aydin',
                'userFullName' => 'Aydin Hassan',
                'lineNumber' => '1',
                'sku' => '4567',
                'qtyRequired' => '1',
                'qtyAllocated' => '1',
                'qtyDespatched' => '0',
                'qtyCancelled' => '0',
                'qtyLost' => '0',
            ],
            [
                'clientCode' => '54',
                'orderNumber' => '000001',
                'customerOrderNumber' => '000001',
                'userId' => 'aydin',
                'userFullName' => 'Aydin Hassan',
                'lineNumber' => '2',
                'sku' => '4568',
                'qtyRequired' => '1',
                'qtyAllocated' => '0',
                'qtyDespatched' => '0',
                'qtyCancelled' => '0',
                'qtyLost' => '0',
            ],
        ];

        $data = $this->reader->current();
        $this->assertSame($expected[0], $data);
        $this->reader->next();
        $data = $this->reader->current();
        $this->assertSame($expected[1], $data);
    }

    public function testGetFields()
    {
        $file = fopen(__DIR__.'/../Fixtures/valid_xml.xml', 'r+b');
        $this->reader = new Xml(
            $file,
            [
                '//orderStatus/order',
                'lines/line',
            ],
            'merge'
        );

        $fields = [
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
        ];

        $this->assertSame($fields, $this->reader->getFields());
    }

    public function testCount()
    {
        $file = fopen(__DIR__.'/../Fixtures/valid_xml.xml', 'r+b');
        $this->reader = new Xml(
            $file,
            [
                '//orderStatus/order',
                'lines/line',
            ],
            'merge'
        );

        $this->assertSame(2, $this->reader->count());
    }

    public function testExceptionIsThrownIsResourceIsNotAResource()
    {
        $this->expectException(
            'InvalidArgumentException'
        );
        $this->expectExceptionMessage(
            'Expected argument to be a stream resource, got "stdClass"'
        );

        new Xml(new \stdClass());
    }

    public function testExceptionIsThrownIfUnrecognizedType()
    {
        $this->expectException(
            'InvalidArgumentException'
        );
        $this->expectExceptionMessage(
            'Type: "notatype" is not supported. Valid types are: "merge, nest"'
        );

        $file = fopen(__DIR__.'/../Fixtures/valid_xml.xml', 'r+b');
        new Xml($file, [], 'notatype');
    }
}
