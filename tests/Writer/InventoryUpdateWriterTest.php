<?php

namespace SixBySix\PortTest\Writer;

use SixBySix\Port\Writer\InventoryUpdateWriter;

/**
 * Class InventoryUpdateWriterTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class InventoryUpdateWriterTest extends \PHPUnit\Framework\TestCase
{
    protected $inventoryUpdateWriter;

    protected $stockItemModel;

    protected $productModel;

    protected $options = [];

    protected function setUp()
    {
        $this->stockItemModel = $this->createMock('\Mage_CatalogInventory_Model_Stock_Item');
        $this->productModel = $this->getMockBuilder('\Mage_Catalog_Model_Product')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return InventoryUpdateWriter
     */
    public function getInventoryWriter()
    {
        return new InventoryUpdateWriter($this->productModel, $this->options);
    }

    public function testCannotSetUnrecognizedUpdateType()
    {
        $this->options['stockUpdateType'] = 'notatype';
        $this->expectException(
            '\InvalidArgumentException'
        );
        $this->expectExceptionMessage(
            "'notatype' is not a valid value for 'stockUpdateType'"
        );

        $this->getInventoryWriter();
    }

    public function testCanSetValidUpdateType()
    {
        $this->options['stockUpdateType'] = 'add';
        $writer = $this->getInventoryWriter();
        $this->assertInstanceOf('SixBySix\Port\Writer\InventoryUpdateWriter', $writer);

        $this->options['stockUpdateType'] = 'set';
        $writer = $this->getInventoryWriter();
        $this->assertInstanceOf('SixBySix\Port\Writer\InventoryUpdateWriter', $writer);
    }

    /**
     * @dataProvider necessaryFieldsProvider
     *
     * @param mixed $field
     * @param mixed $data
     * @param mixed $message
     */
    public function testExceptionIsThrownIfNecessaryFieldsNotFoundInData($field, $data, $message)
    {
        $writer = $this->getInventoryWriter();

        $this->expectException('Port\Exception\WriterException');
        $this->expectExceptionMessage($message);
        $writer->writeItem($data);
    }

    public function necessaryFieldsProvider()
    {
        return [
            ['product_id',  [],                     'No product Id Found'],
            ['qty',         ['product_id' => 2],    'No Quantity found for Product: "2". Using field "qty"'],
        ];
    }

    public function testExceptionIsThrownIfProductCannotBeLoadedBySku()
    {
        $sku = 'PROD1234';
        $data = ['product_id' => $sku, 'qty' => 10];

        $this->productModel
            ->expects($this->once())
            ->method('getIdBySku')
            ->with($sku)
            ->will($this->returnValue(null));

        $writer = $this->getInventoryWriter();

        $message = 'Product not found with SKU: "PROD1234"';
        $this->expectException('Port\Exception\WriterException');
        $this->expectExceptionMessage($message);
        $writer->writeItem($data);
    }

    public function testStockQtyIsSetWhenUpdateModeIsSet()
    {
        $id = 5;
        $data = ['product_id' => $id, 'qty' => 10];

        $this->options['productIdField'] = 'id';

        $this->productModel
            ->expects($this->never())
            ->method('getIdBySku');

        $this->productModel
            ->expects($this->once())
            ->method('load')
            ->with(5);

        $this->productModel
            ->expects($this->once())
            ->method('getData')
            ->with('stock_item')
            ->will($this->returnValue($this->stockItemModel));

        $this->stockItemModel
            ->expects($this->at(0))
            ->method('setData')
            ->with('qty', 10);

        $this->stockItemModel
            ->expects($this->at(2))
            ->method('setData')
            ->with('is_in_stock', 1);

        $this->stockItemModel
            ->expects($this->once())
            ->method('save');

        $writer = $this->getInventoryWriter();
        $writer->writeItem($data);
    }

    public function testStockQtyIsAddedToWhenUpdateModeIsAdd()
    {
        $this->options['stockUpdateType'] = 'add';
        $id = 5;
        $data = ['product_id' => $id, 'qty' => 10];

        $this->options['productIdField'] = 'id';

        $this->productModel
            ->expects($this->never())
            ->method('getIdBySku');

        $this->productModel
            ->expects($this->once())
            ->method('load')
            ->with(5);

        $this->productModel
            ->expects($this->once())
            ->method('getData')
            ->with('stock_item')
            ->will($this->returnValue($this->stockItemModel));

        $this->stockItemModel
            ->expects($this->once())
            ->method('getData')
            ->with('qty')
            ->will($this->returnValue(5));

        $this->stockItemModel
            ->expects($this->at(1))
            ->method('setData')
            ->with('qty', 15);

        $this->stockItemModel
            ->expects($this->at(3))
            ->method('setData')
            ->with('is_in_stock', 1);

        $this->stockItemModel
            ->expects($this->once())
            ->method('save');

        $writer = $this->getInventoryWriter();
        $writer->writeItem($data);
    }

    public function testMagentoSaveExceptionIsThrownIfSaveFails()
    {
        $productId = 2;
        $sku = 'PROD1234';
        $data = ['product_id' => $sku, 'qty' => 10];

        $this->productModel
            ->expects($this->once())
            ->method('getIdBySku')
            ->with($sku)
            ->will($this->returnValue($productId));

        $this->productModel
            ->expects($this->once())
            ->method('load')
            ->with(2);

        $this->productModel
            ->expects($this->once())
            ->method('getData')
            ->with('stock_item')
            ->will($this->returnValue($this->stockItemModel));

        $e = new \Mage_Customer_Exception('Save Failed');
        $this->stockItemModel
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException($e));

        $this->expectException('SixBySix\Port\Exception\MagentoSaveException');
        $this->expectExceptionMessage('Save Failed');
        $writer = $this->getInventoryWriter();
        $writer->writeItem($data);
    }

    public function testPrepareReturnsSelf()
    {
        $writer = $this->getInventoryWriter();
        $this->assertSame($writer, $writer->prepare());
    }
}
