<?php

namespace SixBySix\PortTest\ItemConverter;

use SixBySix\Port\ItemConverter\ItemNesterConverter;

/**
 * Class ItemNestConverterTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class ItemNestConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testSetMappingAccepts1DimensionalArrayOfMappings()
    {
        $mappings = [
            'nestMe1',
            'nestMe2',
        ];

        $itemConvert = $this->getMockBuilder('SixBySix\Port\ItemConverter\ItemNesterConverter')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $itemConvert->setMappings($mappings);

        $expected = [
            'nestMe1' => true,
            'nestMe2' => true,
        ];
        $this->assertSame($expected, $itemConvert->getMappings());
    }

    public function testSetMappingAccepts2DimensionalArrayOfMappings()
    {
        $mappings = [
            ['nestMe1' => false],
            ['nestMe2' => true],
        ];

        $itemConvert = $this->getMockBuilder('SixBySix\Port\ItemConverter\ItemNesterConverter')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $itemConvert->setMappings($mappings);

        $expected = [
            'nestMe1' => false,
            'nestMe2' => true,
        ];
        $this->assertSame($expected, $itemConvert->getMappings());
    }

    public function testSetMappingsUsesTrueIfRemoveArgumentNotBoolean()
    {
        $mappings = [
            ['nestMe1' => new \stdClass()],
            ['nestMe2' => false],
        ];

        $itemConvert = $this->getMockBuilder('SixBySix\Port\ItemConverter\ItemNesterConverter')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $itemConvert->setMappings($mappings);

        $expected = [
            'nestMe1' => true,
            'nestMe2' => false,
        ];
        $this->assertSame($expected, $itemConvert->getMappings());
    }

    public function testSetMappingAcceptsBoth2dAnd3dMappings()
    {
        $mappings = [
            ['nestMe1' => false],
            'nestMe2',
        ];

        $itemConvert = $this->getMockBuilder('SixBySix\Port\ItemConverter\ItemNesterConverter')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $itemConvert->setMappings($mappings);

        $expected = [
            'nestMe1' => false,
            'nestMe2' => true,
        ];
        $this->assertSame($expected, $itemConvert->getMappings());
    }

    public function testDataIsTransformedCorrectly()
    {
        $mappings = [
            ['nestMe1' => false],
            ['nestMe2' => true],
        ];

        $input = [
            'nestMe1' => 'someValue1',
            'nestMe2' => 'someValue2',
            'leaveMeHere' => 'someValue3',
        ];

        $expected = [
            'nestMe1' => 'someValue1',
            'leaveMeHere' => 'someValue3',
            'nested' => [
                [
                    'nestMe1' => 'someValue1',
                    'nestMe2' => 'someValue2',
                ],
            ],
        ];

        $itemConvert = new ItemNesterConverter($mappings, 'nested');
        $output = $itemConvert->convert($input);

        $this->assertSame($expected, $output);
    }

    public function testDataIsTransformedCorrectlyIfArrayNestIsFalse()
    {
        $mappings = [
            ['nestMe1' => false],
            ['nestMe2' => true],
        ];

        $input = [
            'nestMe1' => 'someValue1',
            'nestMe2' => 'someValue2',
            'leaveMeHere' => 'someValue3',
        ];

        $expected = [
            'nestMe1' => 'someValue1',
            'leaveMeHere' => 'someValue3',
            'nested' => [
                'nestMe1' => 'someValue1',
                'nestMe2' => 'someValue2',
            ],
        ];

        $itemConvert = new ItemNesterConverter($mappings, 'nested', false);
        $output = $itemConvert->convert($input);

        $this->assertSame($expected, $output);
    }

    public function testConvertThrowsExceptionIfResultKeyExistsInData()
    {
        $mappings = [
            ['nestMe1' => false],
            ['nestMe2' => true],
        ];

        $input = [
            'nestMe1' => 'someValue1',
            'nestMe2' => 'someValue2',
            'leaveMeHere' => 'someValue3',
            'nested' => new \stdClass(),
        ];

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage("'nested' is already set");
        $itemConvert = new ItemNesterConverter($mappings, 'nested');
        $itemConvert->convert($input);
    }

    public function testIfInputDataMissingNullIsSet()
    {
        $mappings = [
            ['nestMe1' => false],
            ['nestMe2' => true],
        ];

        $input = [
            'nestMe2' => 'someValue2',
        ];

        $itemConvert = new ItemNesterConverter($mappings, 'nested', false);
        $data = $itemConvert->convert($input);

        $expected = [
            'nested' => ['nestMe1' => null, 'nestMe2' => 'someValue2'],
        ];

        $this->assertSame($expected, $data);
    }
}
