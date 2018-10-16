<?php

namespace SixBySix\Port\Service;

use Exception;
use SixBySix\Port\Exception\MagentoSaveException;
use Mage_Catalog_Model_Product;
use Mage_Eav_Model_Entity_Attribute;

/**
 * Class ConfigurableProductService
 * @package SixBySix\Port\Service
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ConfigurableProductService
{

    /**
     * @var Mage_Eav_Model_Entity_Attribute
     */
    protected $eavAttrModel;

    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $productModel;

    /**
     * @param Mage_Eav_Model_Entity_Attribute $eavAttrModel
     * @param Mage_Catalog_Model_Product       $product
     */
    public function __construct(Mage_Eav_Model_Entity_Attribute $eavAttrModel, Mage_Catalog_Model_Product $product)
    {
        $this->eavAttrModel = $eavAttrModel;
        $this->productModel = $product;
    }

    /**
     * @param \Mage_Catalog_Model_Product $product
     * @param string                      $parentSku
     *
     * @throws MagentoSaveException
     */
    public function assignSimpleProductToConfigurable(
        \Mage_Catalog_Model_Product $product,
        $parentSku
    ) {
        $configProduct  = $this->productModel
            ->loadByAttribute('sku', $parentSku);

        if (false === $configProduct) {
            throw new MagentoSaveException(sprintf('Parent product with SKU: "%s" does not exist', $parentSku));
        }

        if ($configProduct->getData('type_id') !== 'configurable') {
            throw new MagentoSaveException(sprintf('Parent product with SKU: "%s" is not configurable', $parentSku));
        }

        $configType = $configProduct->getTypeInstance();
        $attributes = $configType->getConfigurableAttributesAsArray($configProduct);

        $configData = [];
        foreach ($attributes as $attribute) {
            $attributeCode    = $attribute['attribute_code'];
            $configData[]     = [
                'attribute_id'  => $this->eavAttrModel->getIdByCode('catalog_product', $attributeCode),
                'label'         => $product->getAttributeText($attributeCode),
                'value_index'   => $product->getData($attributeCode),
                'pricing_value' => $product->getPrice(),
            ];
        }

        //We wanna keep the old used products as well so we add them to the config too. Their ids are enough.
        $newProductsRelations = [];
        foreach ($configType->getUsedProductIds() as $existingUsedProductId) {
            $newProductsRelations[$existingUsedProductId] = [];
        }

        $newProductsRelations[$product->getId()] = $configData;
        $configProduct->setData('configurable_products_data', $newProductsRelations);

        try {
            $configProduct->save();
        } catch (Exception $e) {
            throw new MagentoSaveException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @param \Mage_Catalog_Model_Product $product
     * @param array                       $configurableAttributes
     *
     * @throws MagentoSaveException
     */
    public function setupConfigurableProduct(\Mage_Catalog_Model_Product $product, array $configurableAttributes)
    {
        $productTypeInstance = $product->getTypeInstance();
        $attributeIds = [];

        //get attribute ID's
        foreach ($configurableAttributes as $attribute) {
            $attributeId = $this->eavAttrModel->getIdByCode('catalog_product', $attribute);

            if (false === $attributeId) {
                throw new MagentoSaveException(
                    sprintf(
                        'Cannot create configurable product with SKU: "%s". Attribute: "%s" does not exist',
                        $product->getData('sku'),
                        $attribute
                    )
                );
            }

            if (!$productTypeInstance->getAttributeById($attributeId)) {
                $msg  = 'Cannot create configurable product with SKU: "%s". Attribute: "%s" is not assigned ';
                $msg .= 'to the attribute set: "%s"';

                throw new MagentoSaveException(
                    sprintf($msg, $product->getData('sku'), $attribute, $product->getData('attribute_set_id'))
                );
            }

            $attributeIds[] = $attributeId;
        }

        //set the attributes that should be configurable for this product
        $productTypeInstance->setUsedProductAttributeIds($attributeIds);
        $configurableAttributesData = $productTypeInstance->getConfigurableAttributesAsArray();

        $product->addData([
            'can_save_configurable_attributes' => true,
            'configurable_attributes_data'     => $configurableAttributesData,
        ]);
    }
}
