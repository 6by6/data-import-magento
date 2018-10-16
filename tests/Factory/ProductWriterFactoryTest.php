<?php

namespace SixBySix\PortTest\Factory;

use SixBySix\Port\Factory\ProductWriterFactory;

/**
 * Class ProductWriterFactoryTest
 * @package SixBySix\PortTest\Factory
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ProductWriterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsInstance()
    {
        $factory = new ProductWriterFactory;
        $this->assertInstanceOf('\SixBySix\Port\Writer\ProductWriter', $factory->__invoke(
            $this->getMock('\Psr\Log\LoggerInterface')
        ));
    }
}
