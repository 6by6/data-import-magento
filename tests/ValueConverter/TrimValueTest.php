<?php

namespace SixBySix\Port\ValueConverter;

/**
 * Class TrimValueTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class TrimValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Trim
     */
    protected $converter;

    protected function setUp()
    {
        $this->converter = new Trim();
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

    public function testTrim()
    {
        $this->assertSame('lol', $this->converter->convert('    lol    '));
    }

    public function testTrimWithCharacterMask()
    {
        $this->converter = new Trim('l');
        $this->assertSame('ol!', $this->converter->convert('lol!'));
    }
}
