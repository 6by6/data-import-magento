<?php

namespace SixBySix\PortTest\Writer;

use SixBySix\Port\Writer\XmlWriter;

/**
 * Class XmlWriterTest.
 *
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class XmlWriterTest extends \PHPUnit\Framework\TestCase
{
    public function testXmlWriter()
    {
        $data = [
            'order' => [
                'orders_info' => [
                    'orders_id' => 1,
                    'customers_id' => 2,
                    'delivery_name' => 'Royal Mail',
                ],
            ],
            'totals' => [
                'ot_total' => '100',
            ],
            'products_info' => [
                [
                    'order_products_id' => 10,
                    'products_quantity' => 4,
                    'is_inventory' => 1,
                ],
                [
                    'order_products_id' => 20,
                    'products_quantity' => 5,
                    'is_inventory' => 0,
                ],
            ],
        ];
        $mappings = [
            'products_info' => 'order_products',
        ];
        $file = tempnam(sys_get_temp_dir(), $this->getName());
        $writer = new XmlWriter($file, $mappings, 'orders');
        $writer->writeItem($data);
        $this->assertFileEquals(__DIR__.'/../Fixtures/xml-order-export.xml', $file);
        unlink($file);
    }
}
