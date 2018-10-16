<?php

namespace SixBySix\PortTest\Exception;

use SixBySix\Port\Exception\MagentoSaveException;

/**
 * Class MagentoSaveExceptionTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class MagentoSaveExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testException()
    {
        $e = new MagentoSaveException('Some Message');
        $this->assertSame('Some Message', $e->getMessage());
        $this->assertSame(0, $e->getCode());
    }
}
