<?php

namespace SixBySix\PortTest\Factory;

use SixBySix\Port\Factory\ShipmentWriterFactory;

/**
 * Class ShipmentWriterFactoryTest.
 *
 * @author  Anthony Bates <anthony@wearejh.com>
 *
 * @internal
 * @coversNothing
 */
final class ShipmentWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testFactoryReturnsInstance()
    {
        $factory = new ShipmentWriterFactory();
        $this->assertInstanceOf('\SixBySix\Port\Writer\Shipment', $factory());
    }
}
