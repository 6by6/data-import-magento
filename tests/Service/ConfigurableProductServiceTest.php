<?php

namespace SixBySix\PortTest\Service;

use Exception;
use SixBySix\Port\Service\ConfigurableProductService;

/**
 * Class ConfigurableProductServiceTest
 * @package SixBySix\PortTest\Service
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ConfigurableProductServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableProductService
     */
    private $service;

    /**
     * @var Mage_Eav_Model_Entity_Attribute
     */
    private $eavAttrModel;

    /**
     * @var Mage_Catalog_Model_Product
     */
    private $productModel;

    public function setUp()
    {
        $this->eavAttrModel = $this->getMock('Mage_Eav_Model_Entity_Attribute');
        $this->productModel = $this->getMock('Mage_Catalog_Model_Product');
        $this->service      = new ConfigurableProductService($this->eavAttrModel, $this->productModel);
    }

    public function testAssignSimpleToConfigThrowsExceptionIfConfigDoesNotExist()
    {
        $this->productModel
            ->expects($this->once())
            ->method('loadByAttribute')
            ->with('sku', 'PARENT1')
            ->will($this->returnValue(false));

        $this->setExpectedException(
            'SixBySix\Port\Exception\MagentoSaveException',
            'Parent product with SKU: "PARENT1" does not exist'
        );
        $this->service->assignSimpleProductToConfigurable($this->productModel, 'PARENT1');
    }

    public function testAssignSimpleToConfigThrowsExceptionIfConfigIsNotActuallyAConfigProduct()
    {
        $configProduct = $this->getMock('Mage_Catalog_Model_Product');
        $configProduct
            ->expects($this->once())
            ->method('getData')
            ->with('type_id')
            ->will($this->returnValue('simple'));

        $simpleProduct = $this->getMock('Mage_Catalog_Model_Product');

        $this->productModel
            ->expects($this->once())
            ->method('loadByAttribute')
            ->with('sku', 'PARENT1')
            ->will($this->returnValue($configProduct));

        $this->setExpectedException(
            'SixBySix\Port\Exception\MagentoSaveException',
            'Parent product with SKU: "PARENT1" is not configurable'
        );

        $this->service->assignSimpleProductToConfigurable($simpleProduct, 'PARENT1');
    }

    public function testAssignSimpleToConfigProduct()
    {
        $configProduct = $this->getMock('Mage_Catalog_Model_Product');
        $this->productModel
            ->expects($this->once())
            ->method('loadByAttribute')
            ->with('sku', 'PARENT1')
            ->will($this->returnValue($configProduct));

        $configProduct
            ->expects($this->once())
            ->method('getData')
            ->with('type_id')
            ->will($this->returnValue('configurable'));

        $simpleProduct = $this->getMock('Mage_Catalog_Model_Product');

        $configType = $this->getMock('Mage_Catalog_Model_Product_Type_Configurable');
        $configProduct
            ->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($configType));

        $configType
            ->expects($this->once())
            ->method('getConfigurableAttributesAsArray')
            ->with($configProduct)
            ->will($this->returnValue([
                ['attribute_code' => 'colour']
            ]));

        $this->eavAttrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'colour')
            ->will($this->returnValue(30));

        $simpleProduct
            ->expects($this->once())
            ->method('getAttributeText')
            ->with('colour')
            ->will($this->returnValue('green'));

        $simpleProduct
            ->expects($this->once())
            ->method('getData')
            ->with('colour')
            ->will($this->returnValue(25));

        $simpleProduct
            ->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue(100));

        $simpleProduct
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(999));

        $configType
            ->expects($this->once())
            ->method('getUsedProductIds')
            ->will($this->returnValue([1, 2]));

        $configProduct
            ->expects($this->once())
            ->method('setData')
            ->with('configurable_products_data', [
                1   => [],
                2   => [],
                999 => [
                    [
                        'attribute_id'  => 30,
                        'label'         => 'green',
                        'value_index'   => 25,
                        'pricing_value' => 100,
                    ]
                ],
            ]);

        $configProduct
            ->expects($this->once())
            ->method('save');

        $this->service->assignSimpleProductToConfigurable($simpleProduct, 'PARENT1');
    }

    public function testAssignSimpleThrowsCorrectExceptionIfSaveFails()
    {
        $configProduct = $this->getMock('Mage_Catalog_Model_Product');
        $this->productModel
            ->expects($this->once())
            ->method('loadByAttribute')
            ->with('sku', 'PARENT1')
            ->will($this->returnValue($configProduct));

        $configProduct
            ->expects($this->once())
            ->method('getData')
            ->with('type_id')
            ->will($this->returnValue('configurable'));

        $simpleProduct = $this->getMock('Mage_Catalog_Model_Product');

        $configType = $this->getMock('Mage_Catalog_Model_Product_Type_Configurable');
        $configProduct
            ->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($configType));

        $configType
            ->expects($this->once())
            ->method('getConfigurableAttributesAsArray')
            ->with($configProduct)
            ->will($this->returnValue([
                ['attribute_code' => 'colour']
            ]));

        $this->eavAttrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'colour')
            ->will($this->returnValue(30));

        $simpleProduct
            ->expects($this->once())
            ->method('getAttributeText')
            ->with('colour')
            ->will($this->returnValue('green'));

        $simpleProduct
            ->expects($this->once())
            ->method('getData')
            ->with('colour')
            ->will($this->returnValue(25));

        $simpleProduct
            ->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue(100));

        $simpleProduct
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(999));

        $configType
            ->expects($this->once())
            ->method('getUsedProductIds')
            ->will($this->returnValue([1, 2]));

        $configProduct
            ->expects($this->once())
            ->method('setData')
            ->with('configurable_products_data', [
                1   => [],
                2   => [],
                999 => [
                    [
                        'attribute_id'  => 30,
                        'label'         => 'green',
                        'value_index'   => 25,
                        'pricing_value' => 100,
                    ]
                ],
            ]);

        $configProduct
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException(new Exception('notbh')));

        $this->setExpectedException('SixBySix\Port\Exception\MagentoSaveException', 'notbh');
        $this->service->assignSimpleProductToConfigurable($simpleProduct, 'PARENT1');
    }

    public function testSetupConfigProductThrowsExceptionIfGivenAttributeDoesNotExist()
    {
        $configProduct = $this->getMock('Mage_Catalog_Model_Product');

        $this->eavAttrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'some_attr')
            ->will($this->returnValue(false));

        $configProduct
            ->expects($this->once())
            ->method('getData')
            ->with('sku')
            ->will($this->returnValue('CONFIG1'));

        $this->setExpectedException(
            'SixBySix\Port\Exception\MagentoSaveException',
            'Cannot create configurable product with SKU: "CONFIG1". Attribute: "some_attr" does not exist'
        );

        $this->service->setupConfigurableProduct($configProduct, ['some_attr']);
    }

    public function testSetupConfigProductThrowsExceptionIfGivenAttributeIsNotInTheAttributeSetAssignedToProduct()
    {
        $configProduct = $this->getMock('Mage_Catalog_Model_Product');
        $configProduct
            ->expects($this->exactly(2))
            ->method('getData')
            ->will($this->returnValueMap([
                ['sku', null, "PROD1"],
                ['attribute_set_id', null, 4],
            ]));

        $this->eavAttrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'some_attr')
            ->will($this->returnValue(20));

        $configType = $this->getMock('Mage_Catalog_Model_Product_Type_Configurable');
        $configProduct
            ->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($configType));

        $configType
            ->expects($this->once())
            ->method('getAttributeById')
            ->with(20)
            ->will($this->returnValue(false));

        $msg  = 'Cannot create configurable product with SKU: "PROD1". Attribute: "some_attr" is not assigned to the ';
        $msg .= 'attribute set: "4"';

        $this->setExpectedException('SixBySix\Port\Exception\MagentoSaveException', $msg);
        $this->service->setupConfigurableProduct($configProduct, ['some_attr', 'some_attr2']);
    }

    public function testSetupConfigProductCallsCorrectMethodsOnProduct()
    {
        $configProduct = $this->getMock('Mage_Catalog_Model_Product');

        $this->eavAttrModel
            ->expects($this->exactly(2))
            ->method('getIdByCode')
            ->will($this->returnValueMap([
                ['catalog_product', 'some_attr', 20],
                ['catalog_product', 'some_attr2', 22],
            ]));

        $configType = $this->getMock('Mage_Catalog_Model_Product_Type_Configurable');
        $configProduct
            ->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($configType));

        $configType
            ->expects($this->any())
            ->method('getAttributeById')
            ->will($this->returnValueMap([
                [20, null, true],
                [22, null, true]
            ]));

        $configType
            ->expects($this->once())
            ->method('setUsedProductAttributeIds')
            ->with([20, 22]);

        $configType
            ->expects($this->once())
            ->method('getConfigurableAttributesAsArray')
            ->will($this->returnValue([['id' => 20], ['id' => 22]]));

        $configProduct
            ->expects($this->once())
            ->method('addData')
            ->with([
                'can_save_configurable_attributes' => true,
                'configurable_attributes_data'     => [['id' => 20], ['id' => 22]]
            ]);

        $this->service->setupConfigurableProduct($configProduct, ['some_attr', 'some_attr2']);
    }
}
