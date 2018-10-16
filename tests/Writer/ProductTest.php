<?php

namespace SixBySix\PortTest\Writer;

use Mockery as m;
use Psr\Log\LoggerInterface;
use SixBySix\Port\Exception\MagentoSaveException;
use SixBySix\Port\Service\AttributeService;
use SixBySix\Port\Service\ConfigurableProductService;
use SixBySix\Port\Service\RemoteImageImporter;
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
    protected $eavAttributeModel;
    protected $attributeService;
    protected $remoteImageImporter;
    protected $configurableProductService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected function setUp()
    {
        $this->productModel = m::mock(\Mage_Catalog_Model_Product::class)
            ->shouldIgnoreMissing()
            ->makePartial();

        $this->remoteImageImporter = m::mock(RemoteImageImporter::class)->makePartial();
        $this->logger = m::mock(LoggerInterface::class);
        $this->attributeService = m::mock(AttributeService::class);

        $this->eavAttributeModel = m::mock(\Mage_Eav_Model_Entity_Attribute::class)->makePartial();
        $this->configurableProductService = m::mock(ConfigurableProductService::class, [
            $this->eavAttributeModel,
            $this->productModel,
        ])
            ->shouldIgnoreMissing()
            ->makePartial();

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
            ->shouldReceive('getDefaultAttributeSetId')
            ->once()
            ->andReturn(1);

        $this->productWriter->prepare();

        $this->assertInstanceOf(Product::class, $this->productWriter);
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
            ->shouldReceive('addData')
            ->once()
            ->with($expected);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(\Mage_Catalog_Model_Product::class, $this->productModel);
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
            ->shouldReceive('setData')
            ->with(['code1' => 'option1', 'code2' => 'option2']);

        $expected = $data;
        unset($expected['attributes']);

        $this->productModel
            ->shouldReceive('addData')
            ->once()
            ->with($expected);

        $this->attributeService
            ->shouldReceive('getAttrCodeCreateIfNotExist')
            ->with('catalog_product', 'code1', 'option1')
            ->andReturn('option1');

        $this->attributeService
            ->shouldReceive('getAttrCodeCreateIfNotExist')
            ->with('catalog_product', 'code2', 'option2')
            ->andReturn('option2');

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(\Mage_Catalog_Model_Product::class, $this->productModel);
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
            ->shouldReceive('addData')
            ->once()
            ->with($expected);

        $this->attributeService
            ->shouldNotReceive('getAttrCodeCreateIfNotExist');

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(\Mage_Catalog_Model_Product::class, $this->productModel);
    }

    /**
     * @expectedException \SixBySix\Port\Exception\MagentoSaveException
     * @expectedExceptionMessage Configurable product with SKU: "PROD1" must have at least one "configurable_attribute"
     * defined
     */
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
            ->shouldReceive('addData')
            ->once()
            ->with($data);

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
            ->shouldReceive('addData')
            ->once()
            ->with($data);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->productModel
            ->shouldReceive('getTypeInstance')
            ->andReturnSelf();

        $this->eavAttributeModel
            ->shouldReceive('getIdByCode')
            ->with('catalog_product', 'Colour')
            ->andReturn(4);

        $this->productModel
            ->shouldReceive('getAttributeById')
            ->with(4)
            ->andReturn(true);

        $this->configurableProductService
            ->shouldReceive('setupConfigurableProduct')
            ->with($this->productModel, ['Colour']);

        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(Product::class, $this->productWriter);
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
            ->shouldReceive('addData')
            ->once()
            ->with($data);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->configurableProductService
            ->shouldNotReceive('assignSimpleProductToConfigurable');

        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(Product::class, $this->productWriter);
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
            ->shouldReceive('addData')
            ->once()
            ->with($data);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->productModel
            ->shouldReceive('loadByAttribute')
            ->with('sku', 'PARENT1')
            ->andReturnSelf();

        $this->productModel
            ->shouldReceive('getData')
            ->once()
            ->with('type_id')
            ->andReturn('configurable');

        $this->productModel
            ->shouldReceive('getTypeInstance')
            ->once()
            ->andReturnSelf();

        $this->productModel
            ->shouldReceive('getConfigurableAttributesAsArray', 'getUsedProductIds')
            ->once()
            ->andReturn([]);

        $this->productModel
            ->shouldReceive('getId')
            ->andReturn(12);

        $this->configurableProductService
            ->shouldReceive('assignSimpleProductToConfigurable')
            ->once()
            ->with($this->productModel, 'PARENT1');

        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(\Mage_Catalog_Model_Product::class, $this->productModel);
    }

    public function testProductIsRemovedAndExceptionIsThrownIfErrorAssigningSimpleToParent()
    {
        $this->expectException(\SixBySix\Port\Exception\MagentoSaveException::class);

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
            ->shouldReceive('addData')
            ->with($data);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->configurableProductService
            ->shouldReceive('assignSimpleProductToConfigurable')
            ->once()
            ->andThrow(new MagentoSaveException('nope'));

        $this->productModel
            ->shouldReceive('delete')
            ->once();

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
                __DIR__.'/../Fixtures/honey.jpg',
                __DIR__.'/../Fixtures/honey.jpg',
            ],
        ];

        $this->productModel
            ->shouldReceive('addData')
            ->once()
            ->with($data);

        $this->productModel
            ->shouldReceive('save')
            ->andReturnSelf();

        $this->productModel
            ->shouldReceive('getSku')
            ->once()
            ->andReturn('PROD1');

        $this->productModel
            ->shouldReceive('addImageToMediaGallery')
            ->andReturnSelf();

        $resource = m::mock(\Mage_Catalog_Model_Resource_Product::class)
            ->shouldIgnoreMissing();

        $this->productModel
            ->shouldReceive('getResource')
            ->andReturn($resource);

        $resource
            ->shouldReceive('save')
            ->with()
            ->andReturnSelf();

        $this->remoteImageImporter
            ->shouldReceive('importImage')
            ->with($this->productModel, 'http://image.com/image1.jpg')
            ->andReturn($this->productModel);

        $this->remoteImageImporter
            ->shouldReceive('importImage')
            ->with($this->productModel, 'http://image.com/image2.jpg')
            ->andReturn($this->productModel);

        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(Product::class, $this->productWriter);
    }

    public function testExceptionIsThrownIfImageCouldNotBeImported()
    {
        $this->expectException(\SixBySix\Port\Exception\MagentoSaveException::class);

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
            ->shouldReceive('addData')
            ->once()
            ->with($data);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->remoteImageImporter
            ->shouldReceive('importImage')
            ->once()
            ->with($this->productModel, 'http://image.com/image1.jpg')
            ->andThrow(new MagentoSaveException());

        $this->productModel
            ->shouldReceive('getSku')
            ->once()
            ->andReturn('PROD1');

        $this->productModel
            ->shouldReceive('addImageToMediaGallery')
            ->andReturnSelf();

        $resource = m::mock(\Mage_Catalog_Model_Resource_Product::class)->makePartial();

        $this->productModel
            ->shouldReceive('getResource')
            ->once()
            ->andReturn($resource);

        $resource->shouldReceive('save')
            ->once()
            ->with($this->productModel)
            ->andThrow(new \RuntimeException());

        $this->productModel
            ->shouldReceive('delete')
            ->once()
            ->andReturnSelf();

        $this->productWriter->writeItem($data);
    }

    public function testMagentoSaveExceptionIsThrownIfSaveFails()
    {
        $this->expectException(\SixBySix\Port\Exception\MagentoSaveException::class);

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
            ->shouldReceive('addData')
            ->with($data);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andThrow(new \Mage_Catalog_Exception('Save failed'));

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
            ->shouldReceive('addData')
            ->with($expected);

        $this->productModel
            ->shouldReceive('getDefaultAttributeSetId')
            ->andReturn(1);

        $this->productModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->productWriter->prepare();
        $this->productWriter->writeItem($data);

        $this->assertInstanceOf(Product::class, $this->productWriter);
    }
}
