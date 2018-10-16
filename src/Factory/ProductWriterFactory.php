<?php

namespace SixBySix\Port\Factory;

use Psr\Log\LoggerInterface;
use SixBySix\Port\Service\AttributeService;
use SixBySix\Port\Service\ConfigurableProductService;
use SixBySix\Port\Service\RemoteImageImporter;
use SixBySix\Port\Writer\Product;

/**
 * Class ProductWriterFactory.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ProductWriterFactory
{
    /**
     * @return Product
     */
    public function __invoke(LoggerInterface $logger)
    {
        $productModel = \Mage::getModel('catalog/product');
        $eavAttrModel = \Mage::getModel('eav/entity_attribute');
        $eavAttrSrcModel = \Mage::getModel('eav/entity_attribute_source_table');

        return new Product(
            $productModel,
            new RemoteImageImporter(),
            new AttributeService($eavAttrModel, $eavAttrSrcModel),
            new ConfigurableProductService($eavAttrModel, \Mage::getModel('catalog/product')),
            $logger
        );
    }
}
