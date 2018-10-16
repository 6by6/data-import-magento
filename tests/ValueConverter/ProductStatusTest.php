<?php

namespace SixBySix\Port\ValueConverter;

/**
 * Class ProductStatusTest.
 *
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class ProductStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductStatus
     */
    protected $converter;

    protected function setup()
    {
        $this->converter = new ProductStatus();
    }

    public function testExceptionIsThrownIfInvalidStatusIsPassed()
    {
        $message = 'Given Product Status: "on-vacation" is not valid. Allowed values: ';
        $message .= '"Enabled", "Disabled"';

        $this->expectException('\Port\Exception\UnexpectedValueException');
        $this->expectExceptionMessage($message);
        $this->converter->convert('on-vacation');
    }

    public function testConvert()
    {
        $this->assertSame(1, $this->converter->convert('Enabled'));
    }

    public function testDefaultValueIsUsedIfNoValueSet()
    {
        $this->assertSame(2, $this->converter->convert(''));
    }
}
