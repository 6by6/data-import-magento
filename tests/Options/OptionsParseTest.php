<?php

namespace SixBySix\PortTest\Options;

/**
 * Class OptionsParseTest
 * @package SixBySix\PortTest\Options
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class OptionsParseTest extends \PHPUnit_Framework_TestCase
{
    protected $optionsTrait;

    public function setUp()
    {
        $this->optionsTrait = $this->getMockForTrait('SixBySix\Port\Options\OptionsParseTrait');
    }

    public function testOptionsParseThrowsExceptionIfInvalidOptionsAreGiven()
    {
        $acceptedOptions = [
            'thisShouldBeAccepted' => 'defaultValue',
        ];

        $options = [
            'thisShouldBeAccepted'  => 'someOption',
            'shouldNotBeAccepted'   => 'notHere',
            'neitherShouldI'        => 'setMe',
        ];

        $message = "'shouldNotBeAccepted', 'neitherShouldI' are not accepted options";
        $this->setExpectedException('\InvalidArgumentException', $message);
        $this->optionsTrait->parseOptions($acceptedOptions, $options);
    }

    public function testDefaultOptionsExist()
    {
        $acceptedOptions = [
            'thisShouldBeAccepted' => 'someCoolOption'
        ];

        $res = $this->optionsTrait->parseOptions($acceptedOptions, []);
        $this->assertEquals($acceptedOptions, $res);
    }

    public function testCanOverWriteDefaultOption()
    {
        $acceptedOptions = [
            'productIdField' => 'sku'
        ];

        $res = $this->optionsTrait->parseOptions($acceptedOptions, ['productIdField' => 'item_id']);
        $this->assertEquals(['productIdField' => 'item_id'], $res);
    }
}
