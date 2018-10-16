<?php

namespace SixBySix\Port\ValueConverter;

/**
 * Class StrtoupperTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class StrtoupperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Strtoupper
     */
    protected $converter;

    protected function setUp()
    {
        $this->converter = new Strtoupper();
    }

    public function testConverterThrowsExceptionIfValueIsNotString()
    {
        $this->expectException(
            'Port\Exception\UnexpectedTypeException'
        );
        $this->expectExceptionMessage(
            'Expected argument of type "string", "stdClass" given'
        );

        $this->converter->convert(new \stdClass());
    }

    public function testConverterUpperCasesStringInput()
    {
        $this->assertSame('UPPERCASE', $this->converter->convert('uppercase'));
    }
}
