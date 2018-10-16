<?php

namespace SixBySix\PortTest\Options;

/**
 * Class OptionsParseTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class OptionsParseTest extends \PHPUnit\Framework\TestCase
{
    protected $optionsTrait;

    protected function setUp()
    {
        $this->optionsTrait = $this->getMockForTrait('SixBySix\Port\Options\OptionsParseTrait');
    }

    public function testOptionsParseThrowsExceptionIfInvalidOptionsAreGiven()
    {
        $acceptedOptions = [
            'thisShouldBeAccepted' => 'defaultValue',
        ];

        $options = [
            'thisShouldBeAccepted' => 'someOption',
            'shouldNotBeAccepted' => 'notHere',
            'neitherShouldI' => 'setMe',
        ];

        $message = "'shouldNotBeAccepted', 'neitherShouldI' are not accepted options";
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage($message);
        $this->optionsTrait->parseOptions($acceptedOptions, $options);
    }

    public function testDefaultOptionsExist()
    {
        $acceptedOptions = [
            'thisShouldBeAccepted' => 'someCoolOption',
        ];

        $res = $this->optionsTrait->parseOptions($acceptedOptions, []);
        $this->assertSame($acceptedOptions, $res);
    }

    public function testCanOverWriteDefaultOption()
    {
        $acceptedOptions = [
            'productIdField' => 'sku',
        ];

        $res = $this->optionsTrait->parseOptions($acceptedOptions, ['productIdField' => 'item_id']);
        $this->assertSame(['productIdField' => 'item_id'], $res);
    }
}
