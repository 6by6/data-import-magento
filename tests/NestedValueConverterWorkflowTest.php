<?php

namespace Ddeboer\DataImport\Tests;

use Port\Reader\ArrayReader;
use SixBySix\Port\NestedValueConverterWorkflow;

/**
 * @internal
 * @coversNothing
 */
final class NestedValueConverterWorkflowTest extends \PHPUnit\Framework\TestCase
{
    public function testCanAddSameValueConvertToMultipleFields()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
        };

        $workflow->addValueConverter(
            [
                'first',
                'last',
            ],
            $valueConverter
        );

        $refObject = new \ReflectionObject($workflow);
        $refProperty = $refObject->getProperty('valueConverters');
        $refProperty->setAccessible(true);
        $converters = $refProperty->getValue($workflow);

        $this->assertCount(2, $converters);
        $this->assertCount(1, $converters['first']);
        $this->assertCount(1, $converters['last']);
        $this->assertSame($valueConverter, $converters['first'][0]);
        $this->assertSame($valueConverter, $converters['last'][0]);
    }

    public function testCanAddValueConverterToOneField()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = (function () {
        });

        $workflow->addValueConverter(
            'first',
            $valueConverter
        );

        $refObject = new \ReflectionObject($workflow);
        $refProperty = $refObject->getProperty('valueConverters');
        $refProperty->setAccessible(true);
        $converters = $refProperty->getValue($workflow);

        $this->assertCount(1, $converters);
        $this->assertCount(1, $converters['first']);
        $this->assertSame($valueConverter, $converters['first'][0]);
    }

    public function testValueConverterOnArray()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            'first',
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'first' => 'James',
            'last' => 'Bond',
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'first' => 'convertedValue',
            'last' => 'Bond',
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterOnNestedProperties()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            [
                'name/first',
                'name/last',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'name' => [
                'first' => 'James',
                'last' => 'Bond',
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'name' => [
                'first' => 'convertedValue',
                'last' => 'convertedValue',
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterOnCollection()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            [
                'name[]/first',
                'name[]/last',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'name' => [
                [
                    'first' => 'James',
                    'last' => 'Bond',
                ],
                [
                    'first' => 'Miss',
                    'last' => 'Moneypenny',
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'name' => [
                [
                    'first' => 'convertedValue',
                    'last' => 'convertedValue',
                ],
                [
                    'first' => 'convertedValue',
                    'last' => 'convertedValue',
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterIgnoresKeyStructureWhichDoesNotExist()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            [
                'name[]/nothere',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'name' => [
                [
                    'first' => 'James',
                    'last' => 'Bond',
                ],
                [
                    'first' => 'Miss',
                    'last' => 'Moneypenny',
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'name' => [
                [
                    'first' => 'James',
                    'last' => 'Bond',
                ],
                [
                    'first' => 'Miss',
                    'last' => 'Moneypenny',
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterIgnoresKeyStructureWhichDoesNotExistAtRoot()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            [
                'nothereroot/nothere',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'name' => [
                [
                    'first' => 'James',
                    'last' => 'Bond',
                ],
                [
                    'first' => 'Miss',
                    'last' => 'Moneypenny',
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'name' => [
                [
                    'first' => 'James',
                    'last' => 'Bond',
                ],
                [
                    'first' => 'Miss',
                    'last' => 'Moneypenny',
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterOnDoubleNestedArray()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            [
                'address/street/street1',
                'address/street/street2',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'address' => [
                'street' => [
                    'street1' => '61 Horsen Ferry Road',
                    'street2' => 'London',
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'address' => [
                'street' => [
                    'street1' => 'convertedValue',
                    'street2' => 'convertedValue',
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterOnNestedCollection()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            [
                'addresses[]/street/street1',
                'addresses[]/street/street2',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'addresses' => [
                [
                    'street' => [
                        'street1' => '61 Horsen Ferry Road',
                        'street2' => 'London',
                    ],
                ],
                [
                    'street' => [
                        'street1' => '62 Horsen Ferry Road',
                        'street2' => 'London',
                    ],
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'addresses' => [
                [
                    'street' => [
                        'street1' => 'convertedValue',
                        'street2' => 'convertedValue',
                    ],
                ],
                [
                    'street' => [
                        'street1' => 'convertedValue',
                        'street2' => 'convertedValue',
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterOnDoubleNestedArrayProperties2()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function () {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            [
                'addresses[]/streets[]/name',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'addresses' => [
                [
                    'streets' => [
                        [
                            'name' => 'Barton Court',
                        ],
                        [
                            'name' => 'Barton Court',
                        ],
                    ],
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'addresses' => [
                [
                    'streets' => [
                        [
                            'name' => 'convertedValue',
                        ],
                        [
                            'name' => 'convertedValue',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterWildCard()
    {
        $workflow = $this->getWorkflow();
        $valueConverter =
            function () {
                return 'convertedValue';
            };

        $workflow->addValueConverter(
            '*',
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'firstName' => 'Mark',
            'lastName' => 'Corrigan',
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'firstName' => 'convertedValue',
            'lastName' => 'convertedValue',
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterNestedWildCard()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function ($value) {
            return 'convertedValue';
        };

        $workflow->addValueConverter(
            'names[]/*',
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'names' => [
                [
                    'first' => 'James',
                    'last' => 'Bond',
                ],
                [
                    'first' => 'Miss',
                    'last' => 'Moneypenny',
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'names' => [
                [
                    'first' => 'convertedValue',
                    'last' => 'convertedValue',
                ],
                [
                    'first' => 'convertedValue',
                    'last' => 'convertedValue',
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    public function testValueConverterWithMultipleWildCards()
    {
        $workflow = $this->getWorkflow();
        $valueConverter = function ($value) {
            return \is_string($value) ? 'convertedValue' : $value;
        };

        $workflow->addValueConverter(
            [
                'names[]/*',
                '*',
            ],
            $valueConverter
        );

        $method = new \ReflectionMethod($workflow, 'convertItem');
        $method->setAccessible(true);

        $data = [
            'someKey' => 'someValue',
            'someOtherKey' => 'someOtherValue',
            'names' => [
                [
                    'first' => 'James',
                    'last' => 'Bond',
                ],
                [
                    'first' => 'Miss',
                    'last' => 'Moneypenny',
                ],
            ],
        ];

        $convertedItem = $method->invoke($workflow, $data);

        $expected = [
            'someKey' => 'convertedValue',
            'someOtherKey' => 'convertedValue',
            'names' => [
                [
                    'first' => 'convertedValue',
                    'last' => 'convertedValue',
                ],
                [
                    'first' => 'convertedValue',
                    'last' => 'convertedValue',
                ],
            ],
        ];

        $this->assertSame($expected, $convertedItem);
    }

    protected function getWorkflow()
    {
        $reader = new ArrayReader([
            [
                'first' => 'James',
                'last' => 'Bond',
            ],
            [
                'first' => 'Miss',
                'last' => 'Moneypenny',
            ],
            [
                'first' => null,
                'last' => 'Doe',
            ],
        ]);

        return new NestedValueConverterWorkflow($reader);
    }
}
