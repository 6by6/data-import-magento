<?php

namespace SixBySix\Port\ValueConverter;

/**
 * Class StrtolowerTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class StrtolowerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Strtolower
     */
    protected $converter;

    protected function setUp()
    {
        $this->converter = new Strtolower();
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
        $this->assertSame('lowercase', $this->converter->convert('LOWERCASE'));
    }
}
