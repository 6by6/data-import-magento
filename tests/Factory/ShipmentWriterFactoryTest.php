<?php

namespace SixBySix\PortTest\Factory;

use SixBySix\Port\Factory\ShipmentWriterFactory;

/**
 * Class ShipmentWriterFactoryTest
 * @package SixBySix\PortTest\Factory
 * @author  Anthony Bates <anthony@wearejh.com>
 */
class ShipmentWriterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsInstance()
    {
        $factory = new ShipmentWriterFactory();
        $this->assertInstanceOf('\SixBySix\Port\Writer\ShipmentWriter', $factory->__invoke());
    }
}
