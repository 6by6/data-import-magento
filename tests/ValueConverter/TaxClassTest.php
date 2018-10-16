<?php

namespace SixBySix\Port\ValueConverter;

use AspectMock\Proxy\InstanceProxy;
use AspectMock\Test;

/**
 * Class TaxClassTest.
 *
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class TaxClassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TaxClass
     */
    protected $converter;

    /**
     * @var InstanceProxy
     */
    protected $mageDouble;

    protected function setup()
    {
        $taxClassSourceProduct = $this->getMockBuilder('\Mage_Tax_Model_Class_Source_Product')
            ->disableOriginalConstructor()
            ->setMethods(['getAllOptions'])
            ->getMock();

        $taxClassSourceProduct
            ->expects($this->once())
            ->method('getAllOptions')
            ->will($this->returnValue([
                ['value' => 2, 'label' => 'Taxable Goods'],
                ['value' => 4, 'label' => 'Shipping'],
            ]));

        $this->mageDouble = Test::double('Mage', ['getSingleton' => $taxClassSourceProduct]);
        $this->converter = new TaxClass();
    }

    public function testExceptionIsThrownIfInvalidTaxClassIsPassed()
    {
        $message = 'Given Tax-Class: "no-tax-yeah-right" is not valid. Allowed values: ';
        $message .= '"Taxable Goods", "Shipping"';

        $this->expectException('\Port\Exception\UnexpectedValueException');
        $this->expectExceptionMessage($message);
        $this->converter->convert('no-tax-yeah-right');
        $this->mageDouble->verifyInvoked('getSingleton', ['tax/class_source_product']);
    }

    public function testConvert()
    {
        $this->assertSame(2, $this->converter->convert('Taxable Goods'));
        $this->mageDouble->verifyInvoked('getSingleton', ['tax/class_source_product']);
    }

    public function testDefaultValueIsUsedIfNoValueSet()
    {
        $this->assertSame(2, $this->converter->convert(''));
        $this->mageDouble->verifyInvoked('getSingleton', ['tax/class_source_product']);
    }
}
