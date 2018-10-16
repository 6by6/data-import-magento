<?php

namespace SixBySix\PortTest\ItemConverter;

use SixBySix\Port\ItemConverter\RemoveUnwantedFieldsConverter;

/**
 * Class RemoveUnwantedFieldsConverterTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class RemoveUnwantedFieldsConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testConvert()
    {
        $input = [
            'foo' => 'bar',
            'keepMe1' => 'foo',
            'keepMe2' => 'bar',
        ];

        $fieldsToKeep = ['keepMe1', 'keepMe2'];
        $converter = new RemoveUnwantedFieldsConverter($fieldsToKeep);

        $output = $converter->convert($input);

        $expected = [
            'keepMe1' => 'foo',
            'keepMe2' => 'bar',
        ];
        $this->assertSame($expected, $output);
    }

    public function testNestedFieldsConvert()
    {
        $input = [
            'foo' => 'bar',
            'keepMe1' => 'foo',
            'items' => [
                [
                    'one' => 'one',
                    'two' => 'two',
                    'notNeeded' => 'deleteMe',
                ],
                [
                    'one' => 'one',
                    'two' => 'two',
                    'doNotNeedThisEither' => 'deleteMe',
                ],
            ],
        ];

        $fieldsToKeep = [
            'keepMe1',
            'items' => [
                'one',
                'two',
            ],
        ];
        $converter = new RemoveUnwantedFieldsConverter($fieldsToKeep);

        $expected = [
            'keepMe1' => 'foo',
            'items' => [
                [
                    'one' => 'one',
                    'two' => 'two',
                ],
                [
                    'one' => 'one',
                    'two' => 'two',
                ],
            ],
        ];

        $output = $converter->convert($input);
        $this->assertSame($expected, $output);
    }

    public function testConverterThrowsExceptionIfInputNotArray()
    {
        $converter = new RemoveUnwantedFieldsConverter([]);
        $this->expectException(
            'Port\Exception\UnexpectedTypeException'
        );
        $this->expectExceptionMessage(
            'Expected argument of type "array", "stdClass" given'
        );
        $converter->convert(new \stdClass());
    }

    public function testNestedItemWhichDoesNotExistInInputDataIsReplacedWithEmptyArray()
    {
        $fieldsToKeep = [
            'items' => [
                'one',
            ],
        ];
        $converter = new RemoveUnwantedFieldsConverter($fieldsToKeep);
        $input = [];

        $this->assertSame(['items' => []], $converter->convert($input));
    }

    public function testExceptionIsThrownIfNestDataIsNotAnArray()
    {
        $fieldsToKeep = [
            'items' => [
                'one',
            ],
        ];

        $converter = new RemoveUnwantedFieldsConverter($fieldsToKeep);
        $input = [
            'items' => new \stdClass(),
        ];

        $this->expectException(
            'Port\Exception\UnexpectedTypeException'
        );
        $this->expectExceptionMessage(
            'Expected argument of type "array", "stdClass" given'
        );
        $converter->convert($input);
    }

    public function testNestedItemIsPopulatedWithDefaultValueIfRequiredFieldDoesNotExist()
    {
        $fieldsToKeep = [
            'items' => [
                'one',
            ],
        ];
        $converter = new RemoveUnwantedFieldsConverter($fieldsToKeep);

        $input = [
            'items' => [
                [],
            ],
        ];

        $expected = [
            'items' => [
                ['one' => ''],
            ],
        ];

        $this->assertSame($expected, $converter->convert($input));
    }

    public function testFirstLevelDataIsPopulatedWithDefaultValueIfRequiredFieldDoesNotExist()
    {
        $fieldsToKeep = [
            'name',
        ];
        $converter = new RemoveUnwantedFieldsConverter($fieldsToKeep);

        $input = [];

        $expected = [
            'name' => '',
        ];

        $this->assertSame($expected, $converter->convert($input));
    }
}
