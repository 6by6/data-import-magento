<?php

namespace SixBySix\Port\ValueConverter;

/**
 * Class UcwordsTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class UcwordsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Ucwords
     */
    protected $converter;

    protected function setUp()
    {
        $this->converter = new Ucwords();
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
        $this->assertSame(
            'All These Words Should Begin With A Capital Letter',
            $this->converter->convert('all these words should begin with a capital letter')
        );
    }
}
