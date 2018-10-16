<?php

namespace SixBySix\Port\ValueConverter;

/**
 * Class ProductVisibilityTest.
 *
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class ProductVisibilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductVisibility
     */
    protected $converter;

    protected function setup()
    {
        $this->converter = new ProductVisibility();
    }

    public function testExceptionIsThrownIfInvalidVisibilityIsPassed()
    {
        $message = 'Given Product Visibility: "illusive" is not valid. Allowed values: ';
        $message .= '"Not Visible Individually", "Catalog", "Search", "Catalog, Search"';

        $this->expectException('\Port\Exception\UnexpectedValueException');
        $this->expectExceptionMessage($message);
        $this->converter->convert('illusive');
    }

    public function testConvert()
    {
        $this->assertSame(4, $this->converter->convert('Catalog, Search'));
    }

    public function testDefaultValueIsUsedIfNoValueSet()
    {
        $this->assertSame(1, $this->converter->convert(''));
    }
}
