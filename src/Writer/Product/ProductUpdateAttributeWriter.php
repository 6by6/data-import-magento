<?php
namespace SixBySix\Port\Writer\Product;

use Ddeboer\DataImport\Exception\WriterException;
use Ddeboer\DataImport\Writer\AbstractWriter;
use SixBySix\Port\Exception\MagentoSaveException;

/**
 * Class ProductUpdateAttributeWriter
 * @author Adam Paterson <hello@adampaterson.co.uk>
 * @package SixBySix\Port\Writer\Product
 */
class ProductUpdateAttributeWriter extends AbstractWriter
{
    /**
     * @var \Mage_Catalog_Model_Product
     */
    protected $productModel;

    /**
     * @param \Mage_Catalog_Model_Product $productModel
     */
    public function __construct(\Mage_Catalog_Model_Product $productModel)
    {
        $this->productModel = $productModel;
    }

    /**
     * Write item if product exists
     *
     * @param array $item
     * @throws \SixBySix\Port\Exception\MagentoSaveException
     */
    public function writeItem(array $item)
    {
        $productModel = clone $this->productModel;
        $sku          = $item['sku'];
        $product      = $productModel->loadByAttribute('sku', $sku);

        if (!$product) {
            throw new WriterException(sprintf('Product with SKU: %s does not exist in Magento', $sku));
        }

        $product->addData($item);

        try {
            $product->save();
        } catch (\Mage_Core_Exception $e) {
            $message = $e->getMessage();
            throw new MagentoSaveException($message);
        }
    }
}
