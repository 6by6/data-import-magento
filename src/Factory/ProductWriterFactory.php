<?php

namespace SixBySix\Port\Factory;

use SixBySix\Port\Service\AttributeService;
use SixBySix\Port\Service\ConfigurableProductService;
use SixBySix\Port\Service\RemoteImageImporter;
use SixBySix\Port\Writer\ProductWriter;
use Psr\Log\LoggerInterface;

/**
 * Class ProductWriterFactory
 * @package SixBySix\Port\Factory
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ProductWriterFactory
{
    /**
     * @return ProductWriter
     */
    public function __invoke(LoggerInterface $logger)
    {
        $productModel           = \Mage::getModel('catalog/product');
        $eavAttrModel           = \Mage::getModel('eav/entity_attribute');
        $eavAttrSrcModel        = \Mage::getModel('eav/entity_attribute_source_table');

        return new ProductWriter(
            $productModel,
            new RemoteImageImporter,
            new AttributeService($eavAttrModel, $eavAttrSrcModel),
            new ConfigurableProductService($eavAttrModel, \Mage::getModel('catalog/product')),
            $logger
        );
    }
}
