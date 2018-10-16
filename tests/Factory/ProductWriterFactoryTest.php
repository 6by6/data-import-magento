<?php

namespace SixBySix\PortTest\Factory;

use SixBySix\Port\Factory\ProductWriterFactory;

/**
 * Class ProductWriterFactoryTest.
 *
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class ProductWriterFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testFactoryReturnsInstance()
    {
        $factory = new ProductWriterFactory();
        $this->assertInstanceOf('\SixBySix\Port\Writer\Product', $factory->__invoke(
            $this->createMock('\Psr\Log\LoggerInterface')
        ));
    }
}
