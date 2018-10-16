<?php

namespace SixBySix\PortTest\Writer;

use Psr\Log\LoggerInterface;
use SixBySix\Port\Exception\MagentoSaveException;
use SixBySix\Port\Writer\Product;

/**
 * Class ProductTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Product
     */
    protected $productWriter;
    protected $productModel;
    protected $attributeService;
    protected $remoteImageImporter;
    protected $configurableProductService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected function setUp()
    {
        $this->productModel = $this->getMock('\Mage_Catalog_Model_Product', [], [], '', false);
        $this->remoteImageImporter = $this->createMock('\SixBySix\Port\Service\RemoteImageImporter');
        $this->logger = $this->createMock('\Psr\Log\LoggerInterface');

        $this->attributeService = $this->getMockBuilder('SixBySix\Port\Service\AttributeService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableProductService =
            $this->getMockBuilder('\SixBySix\Port\Service\ConfigurableProductService')
                ->disableOriginalConstructor()
                ->getMock();

        $this->productWriter = new Product(
            $this->productModel,
            $this->remoteImageImporter,
            $this->attributeService,
            $this->configurableProductService,
            $this->logger
        );
    }

    public function testPrepareMethodSetsUpDataCorrectly()
    {
        $this->productModel
            ->expects($this->once())
            ->method('getDefaultAttributeSetId')
            ->will($this->returnValue(1));

        $this->productWriter->prepare();
    }

    public function testWriteItemSuccessfullySaves()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attributes' => [],
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
        ];

        $expected = $data;
        unset($expected['attributes']);
        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($expected);

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->productWriter->writeItem($data);
    }

    public function testWriteWithAttributesDelegatesToAttributeService()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attributes' => [
                'code1' => 'option1',
                'code2' => 'option2',
            ],
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
        ];

        $this->productModel
            ->expects($this->at(0))
            ->method('setData')
            ->with('code1', 'option1');

        $this->productModel
            ->expects($this->at(1))
            ->method('setData')
            ->with('code2', 'option2');

        $expected = $data;
        unset($expected['attributes']);
        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($expected);

        $this->attributeService
            ->expects($this->at(0))
            ->method('getAttrCodeCreateIfNotExist')
            ->with('catalog_product', 'code1', 'option1')
            ->will($this->returnValue('option1'));

        $this->attributeService
            ->expects($this->at(1))
            ->method('getAttrCodeCreateIfNotExist')
            ->with('catalog_product', 'code2', 'option2')
            ->will($this->returnValue('option2'));

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->productWriter->writeItem($data);
    }

    public function testWriteItemWithNullAttributesAreSkipped()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attributes' => [
                'code1' => null,
            ],
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
        ];

        $expected = $data;
        unset($expected['attributes']);
        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($expected);

        $this->attributeService
            ->expects($this->never())
            ->method('getAttrCodeCreateIfNotExist');

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->productWriter->writeItem($data);
    }

    public function testCreateConfigurableProductThrowsExceptionIfNoAttributesSpecified()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'configurable_attributes' => [],
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'configurable',
            'url_key' => null,
            'sku' => 'PROD1',
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $this->expectException(
            '\SixBySix\Port\Exception\MagentoSaveException'
        );
        $this->expectExceptionMessage(
            'Configurable product with SKU: "PROD1" must have at least one "configurable_attribute" defined'
        );

        $this->productWriter->writeItem($data);
    }

    public function testCreateConfigurableProductDelegatesToConfigService()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'configurable_attributes' => ['Colour'],
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'configurable',
            'url_key' => null,
            'sku' => 'PROD1',
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->configurableProductService
            ->expects($this->once())
            ->method('setupConfigurableProduct')
            ->with($this->productModel, ['Colour']);

        $this->productWriter->writeItem($data);
    }

    public function testSimpleProductWithEmptyParentIsNotConfigured()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
            'sku' => 'PROD1',
            'parent_sku' => '',
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->configurableProductService
            ->expects($this->never())
            ->method('assignSimpleProductToConfigurable');

        $this->productWriter->writeItem($data);
    }

    public function testSimpleProductWithParentIsConfigured()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
            'sku' => 'PROD1',
            'parent_sku' => 'PARENT1',
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->configurableProductService
            ->expects($this->once())
            ->method('assignSimpleProductToConfigurable')
            ->with($this->productModel, 'PARENT1');

        $this->productWriter->writeItem($data);
    }

    public function testProductIsRemovedAndExceptionIsThrownIfErrorAssigningSimpleToParent()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
            'sku' => 'PROD1',
            'parent_sku' => 'PARENT1',
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->configurableProductService
            ->expects($this->once())
            ->method('assignSimpleProductToConfigurable')
            ->with($this->productModel, 'PARENT1')
            ->will($this->throwException(new MagentoSaveException('nope')));

        $this->productModel
            ->expects($this->once())
            ->method('delete');

        $this->expectException(
            '\SixBySix\Port\Exception\MagentoSaveException'
        );
        $this->expectExceptionMessage(
            'nope'
        );

        $this->productWriter->writeItem($data);
    }

    public function testImagesAreImported()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
            'sku' => 'PROD1',
            'images' => [
                'http://image.com/image1.jpg',
                'http://image.com/image2.jpg',
            ],
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->remoteImageImporter
            ->expects($this->at(0))
            ->method('importImage')
            ->with($this->productModel, 'http://image.com/image1.jpg');

        $this->remoteImageImporter
            ->expects($this->at(1))
            ->method('importImage')
            ->with($this->productModel, 'http://image.com/image2.jpg');

        $this->productWriter->writeItem($data);
    }

    public function testExceptionIsThrownIfImageCouldNotBeImported()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
            'sku' => 'PROD1',
            'images' => ['http://image.com/image1.jpg'],
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $this->productModel
            ->expects($this->once())
            ->method('save');

        $this->remoteImageImporter
            ->expects($this->once())
            ->method('importImage')
            ->with($this->productModel, 'http://image.com/image1.jpg')
            ->will($this->throwException(new \RuntimeException('nope!')));

        $this->productModel
            ->expects($this->once())
            ->method('delete');

        $this->expectException(
            '\SixBySix\Port\Exception\MagentoSaveException'
        );
        $this->expectExceptionMessage(
            'Error importing image for product with SKU: "PROD1". Error: "nope!"'
        );

        $this->productWriter->writeItem($data);
    }

    public function testMagentoSaveExceptionIsThrownIfSaveFails()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attribute_set_id' => 0,
            'stock_data' => [],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($data);

        $e = new \Mage_Customer_Exception('Save Failed');
        $this->productModel
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException($e));

        $this->expectException('SixBySix\Port\Exception\MagentoSaveException');
        $this->expectExceptionMessage('Save Failed');
        $this->productWriter->writeItem($data);
    }

    public function testDefaultsAreUsedForProductIfNotExistInInputData()
    {
        $data = [
            'name' => 'Product 1',
            'description' => 'Description',
        ];

        $expected = [
            'name' => 'Product 1',
            'description' => 'Description',
            'attribute_set_id' => null,
            'stock_data' => [
                'manage_stock' => 1,
                'use_config_manage_stock' => 1,
                'qty' => 0,
                'min_qty' => 0,
                'use_config_min_qty' => 1,
                'min_sale_qty' => 1,
                'use_config_min_sale_qty' => 1,
                'max_sale_qty' => 10000,
                'use_config_max_sale_qty' => 1,
                'is_qty_decimal' => 0,
                'backorders' => 0,
                'use_config_backorders' => 1,
                'notify_stock_qty' => 1,
                'use_config_notify_stock_qty' => 1,
                'enable_qty_increments' => 0,
                'use_config_enable_qty_inc' => 1,
                'qty_increments' => 0,
                'use_config_qty_increments' => 1,
                'is_in_stock' => 0,
                'low_stock_date' => null,
                'stock_status_changed_auto' => 0,
            ],
            'weight' => '0',
            'status' => '1',
            'tax_class_id' => 2,
            'website_ids' => [1],
            'type_id' => 'simple',
            'url_key' => null,
        ];

        $this->productModel
            ->expects($this->once())
            ->method('addData')
            ->with($expected);

        $e = new \Mage_Customer_Exception('Save Failed');
        $this->productModel
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException($e));

        $this->expectException('SixBySix\Port\Exception\MagentoSaveException');
        $this->expectExceptionMessage('Save Failed');
        $this->productWriter->prepare();
        $this->productWriter->writeItem($data);
    }
}
