<?php

namespace SixBySix\PortTest\Service;

use SixBySix\Port\Service\RemoteImageImporter;

/**
 * Class RemoteImageImporterTest.
 *
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class RemoteImageImporterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RemoteImageImporter
     */
    private $importer;

    /**
     * @var \Mage_Catalog_Model_Product
     */
    private $product;

    protected function setup()
    {
        $this->importer = new RemoteImageImporter();
        $this->product = $this->getMockBuilder('\Mage_Catalog_Model_Product')->getMock([], [], '', false);
    }

    public function testImportImage()
    {
        $url = __DIR__.'/../Fixtures/honey.jpg';
        $path = realpath(__DIR__.'/../../');
        $path .= '/vendor/firegento/magento/media/import/efba9ed5cc7df0bb6fc031bde060ffd4.jpg';

        $this->product
            ->expects($this->once())
            ->method('addImageToMediaGallery')
            ->with($path, ['thumbnail', 'small_image', 'image'], true, false);

        $resource = $this->getMockBuilder('Mage_Core_Model_Mysql4_Abstract')
            ->disableOriginalConstructor()
            ->getMock();

        $this->product
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($resource));

        $resource
            ->expects($this->once())
            ->method('save')
            ->with($this->product);

        $this->importer->importImage($this->product, $url);
    }

    public function testImportThrowsExceptionIfImageFailsToDownload()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('URL returned nothing: "notaurl"');

        $url = 'notaurl';

        $this->importer->importImage($this->product, $url);
    }

    public function testImportThrowsExceptionIfImageFailsToImport()
    {
        $url = __DIR__.'/../Fixtures/honey.jpg';
        $path = realpath(__DIR__.'/../../');
        $path .= '/vendor/firegento/magento/media/import/efba9ed5cc7df0bb6fc031bde060ffd4.jpg';

        $this->product
            ->expects($this->once())
            ->method('addImageToMediaGallery')
            ->with($path, ['thumbnail', 'small_image', 'image'], true, false);

        $resource = $this->getMockBuilder('Mage_Core_Model_Mysql4_Abstract')
            ->disableOriginalConstructor()
            ->getMock();

        $this->product
            ->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($resource));

        $resource
            ->expects($this->once())
            ->method('save')
            ->with($this->product)
            ->will($this->throwException(new \Mage_Core_Exception('nahhhh')));

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('nahhhh');
        $this->importer->importImage($this->product, $url);

        unlink($path);
        rmdir(\dirname($path));
    }
}
