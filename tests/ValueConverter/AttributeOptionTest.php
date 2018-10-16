<?php

namespace SixBySix\Port\ValueConverter;

/**
 * Class AttributeOption.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class AttributeOptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeOption
     */
    protected $converter;

    protected function setUp()
    {
        $attributeCode = 'colour';
        $options = [
            '1' => 'Red',
            '2' => 'Purple',
            '3' => 'Orange',
            '4' => 'Green',
        ];

        $this->converter = new AttributeOption($attributeCode, $options);
    }

    public function testConverterReturnsCorrectValueForOptionKey()
    {
        $this->assertSame('Purple', $this->converter->convert(2));
    }

    public function testConverterThrowsExceptionIfKeyNotExists()
    {
        $this->expectException(
            'Port\Exception\UnexpectedValueException'
        );
        $this->expectExceptionMessage(
            '"6" does not appear to be a valid attribute option for "colour"'
        );

        $this->converter->convert(6);
    }

    public function testConverterReturnsEmptyStringIfOptionNotFound()
    {
        $attributeCode = 'colour';
        $options = [
            '1' => 'Red',
            '2' => 'Purple',
            '3' => 'Orange',
            '4' => 'Green',
        ];

        $this->converter = new AttributeOption($attributeCode, $options, [
            'returnEmptyStringIfOptionNotExist' => true,
        ]);

        $this->assertSame('', $this->converter->convert(6));
    }
}
