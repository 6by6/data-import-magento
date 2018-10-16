<?php

namespace SixBySix\PortTest\Writer;

use Mockery as m;
use SixBySix\Port\Exception\MagentoSaveException;
use SixBySix\Port\Writer\CustomerWriter;

/**
 * Class CustomerWriterTest.
 *
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class CustomerWriterTest extends \PHPUnit\Framework\TestCase
{
    protected $customerWriter;
    protected $customerModel;

    protected function setUp()
    {
        $this->customerModel = m::mock('\Mage_Customer_Model_Customer')
            ->shouldIgnoreMissing()
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        $this->customerWriter = new CustomerWriter($this->customerModel);
    }

    public function testMagentoModelIsSaved()
    {
        $data = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
        ];

        $this->customerModel
            ->shouldReceive('setData')
            ->with($data)
            ->andReturn($this->customerModel);

        $this->customerModel
            ->shouldReceive('setId')
            ->andReturn($this->customerModel);

        $this->customerModel
            ->shouldReceive('save')
            ->andReturn($this->customerModel);

        $this->customerModel
            ->shouldReceive('getPrimaryAddresses')
            ->andReturn([]);

        $this->customerModel
            ->shouldReceive('getAdditionalAddresses')
            ->andReturn([]);

        $customerWriter = $this->customerWriter->writeItem($data);

        $this->assertInstanceOf(CustomerWriter::class, $customerWriter);
    }

    public function testCustomerIsSavedWithAddressWhichHasRegionId()
    {
        $addressData = [
            'street' => 'Pilcher Gate',
            'town' => 'Nottingham',
            'country_id' => 'UK',
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
            'region' => 'Nottinghamshire',
        ];

        $data = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
            'address' => [
                $addressData,
            ],
        ];

        $this->customerModel
            ->shouldReceive('getPrimaryAddresses', 'getDefaultBilling', 'getDefaultShipping', 'getAdditionalAddresses')
            ->once()
            ->andReturn([]);

        $this->customerModel
            ->shouldReceive('getIdFieldName')
            ->andReturn('entity_id');

        $addressModel = m::mock('\Mage_Customer_Model_Address')->makePartial();
        $addressModel
            ->shouldReceive('getIdFieldName')
            ->andReturn('entity_id');

        $addressModel
            ->shouldReceive('save')
            ->andReturn($addressModel);

        $addressModel->shouldReceive('setIsDefaultShipping', 'setIsDefaultBilling');

        $this->customerModel
            ->shouldReceive('addAddress')
            ->with($addressModel)
            ->andReturnSelf();

        $addressCollection = m::mock(Mage_Customer_Model_Entity_Address_Collection::class)->makePartial();
        $addressCollection->shouldReceive('addItem')->andReturnSelf();
        $addressCollection->shouldReceive('getItems')->andReturn([]);

        $this->customerModel
            ->shouldReceive('getAddressesCollection')
            ->andReturn($addressCollection);

        $directoryResourceModel = m::mock('\Mage_Directory_Model_Resource_Region_Collection');
        $directoryResourceModel->shouldReceive('getIterator')
            ->andReturn(new \ArrayIterator([]));

        $this->customerModel
            ->shouldReceive('save')
            ->andReturn($this->customerModel);

        $resource = m::mock(\Mage_Customer_Model_Resource_Customer::class)
            ->shouldIgnoreMissing()
            ->andReturnSelf();

        $this->customerModel
            ->shouldReceive('_getResource')
            ->andReturn($resource);

        $this->customerWriter = new CustomerWriter($this->customerModel, $addressModel, $directoryResourceModel);
        $writer = $this->customerWriter->writeItem($data);

        $this->assertInstanceOf(CustomerWriter::class, $writer);
    }

    public function testCustomerIsSavedWithAddressWhichHasNoRegionId()
    {
        $addressData = [
            'street' => 'Pilcher Gate',
            'town' => 'Nottingham',
            'country_id' => 'UK',
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
            'region' => 'Nottinghamshire',
        ];

        $data = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
            'address' => [
                $addressData,
            ],
        ];

        $customerData = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
        ];

        $this->customerModel
            ->shouldReceive('setData')
            ->once()
            ->with($customerData)
            ->andReturn($this->customerModel);

        $region = m::mock(\Mage_Directory_Model_Region::class);
        $region
            ->shouldReceive('getData')
            ->with('country_id')
            ->andReturn('UK');

        $region
            ->shouldReceive('getData')
            ->with('name')
            ->andReturn('Nottinghamshire');

        $region
            ->shouldReceive('getId')
            ->andReturn(23);

        $addressModel = m::mock('\Mage_Customer_Model_Address')->makePartial();
        $addressModel->shouldReceive('setIsDefaultShipping', 'setIsDefaultBilling')
            ->with(true)
            ->andReturnSelf();

        $directoryResourceModel = m::mock('\Mage_Directory_Model_Resource_Region_Collection');
        $directoryResourceModel->shouldReceive('getIterator')
            ->andReturn(new \ArrayIterator([$region]));

        $this->customerWriter = new CustomerWriter(
            $this->customerModel,
            $addressModel,
            $directoryResourceModel
        );

        $addressModel
            ->shouldReceive('setData')
            ->once()
            ->with($addressData);

        $addressModel
            ->shouldReceive('setId')
            ->once()
            ->andReturnSelf();

        $this->customerModel
            ->shouldReceive('addAddress')
            ->with($addressModel)
            ->andReturnSelf();

        $addressCollection = m::mock(Mage_Customer_Model_Entity_Address_Collection::class)->makePartial();
        $addressCollection->shouldReceive('addItem')->andReturnSelf();
        $addressCollection->shouldReceive('getItems')->andReturn([]);

        $this->customerModel
            ->shouldReceive('getAddressesCollection')
            ->andReturn($addressCollection);

        $this->customerModel
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $this->customerModel
            ->shouldReceive('getIdFieldName')
            ->andReturn('entity_id');

        $this->customerModel
            ->shouldReceive('setId')
            ->with(1)
            ->andReturnSelf();

        $this->customerModel
            ->shouldReceive('getPrimaryAddresses')
            ->once()
            ->andReturn([]);

        $this->customerModel
            ->shouldReceive('getAdditionalAddresses')
            ->once()
            ->andReturn([]);

        $writer = $this->customerWriter->writeItem($data);

        $this->assertInstanceOf(CustomerWriter::class, $writer);
    }

    public function testRegions()
    {
        $directoryResourceModel = $this->getMockBuilder('\Mage_Directory_Model_Resource_Region_Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $region1 = $this->getMockRegionModel(['country_id' => 'UK', 'name' => 'Nottinghamshire',   'id' => 1]);
        $region2 = $this->getMockRegionModel(['country_id' => 'US', 'name' => 'Oregon',            'id' => 2]);
        $region3 = $this->getMockRegionModel(['country_id' => 'US', 'name' => 'California',        'id' => 3]);

        $regions = new \ArrayIterator([
            $region1,
            $region2,
            $region3,
        ]);

        $directoryResourceModel
            ->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue($regions));

        $this->customerWriter = $this->getMockBuilder('SixBySix\Port\Writer\CustomerWriter')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $expected = [
            'UK' => [
                'nottinghamshire' => 1,
            ],
            'US' => [
                'oregon' => 2,
                'california' => 3,
            ],
        ];

        $processed = $this->customerWriter->processRegions($directoryResourceModel);
        $this->assertSame($expected, $processed);
    }

    public function testLookUpRegion()
    {
        $this->customerWriter = $this->getMockBuilder('SixBySix\Port\Writer\CustomerWriter')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $regions = [
            'UK' => [
                'nottinghamshire' => 1,
            ],
            'US' => [
                'oregon' => 2,
                'california' => 3,
            ],
        ];

        $refObject = new \ReflectionObject($this->customerWriter);
        $refProperty = $refObject->getProperty('regions');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->customerWriter, $regions);

        $this->assertSame(1, $this->customerWriter->lookUpRegion('nottinghamshire', 'UK', 'Some Name'));
        $this->assertSame(2, $this->customerWriter->lookUpRegion('oregon', 'US', 'Some Name'));
        $this->assertSame(3, $this->customerWriter->lookUpRegion('california', 'US', 'Some Name'));
        $this->assertFalse($this->customerWriter->lookUpRegion('california', 'UK', 'Some Name'));
        $this->assertFalse($this->customerWriter->lookUpRegion('california', 'AU', 'Some Name'));
    }

    public function testProcessRegionsIsCalledIfAddressModelIsPresent()
    {
        $this->customerWriter = $this->getMockBuilder('SixBySix\Port\Writer\CustomerWriter')
            ->disableOriginalConstructor()
            ->setMethods(['processRegions'])
            ->getMock();

        $directoryResourceModel = $this->getMockBuilder('\Mage_Directory_Model_Resource_Region_Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerWriter
            ->expects($this->once())
            ->method('processRegions')
            ->with($directoryResourceModel)
            ->will($this->returnValue([]));

        $addressModel = $this->createMock('\Mage_Customer_Model_Address');

        $this->customerWriter->__construct($this->customerModel, $addressModel, $directoryResourceModel);
    }

    public function testMagentoSaveExceptionIsThrownIfSaveFails()
    {
        $this->expectException(\SixBySix\Port\Exception\MagentoSaveException::class);

        $data = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
            'email' => 'aydin@hotmail.co.uk',
        ];

        $this->customerModel
            ->shouldReceive('setData')
            ->once()
            ->with($data);

        $this->customerModel
            ->shouldReceive('getIdFieldName')
            ->andReturn('entity_id');

        $this->customerModel
            ->shouldReceive('save')
            ->andThrow(new MagentoSaveException());

        $this->customerModel
            ->shouldReceive('getPrimaryAddresses')
            ->once()
            ->andReturn([]);

        $this->customerModel
            ->shouldReceive('getAdditionalAddresses')
            ->once()
            ->andReturn([]);

        $this->customerWriter->writeItem($data);
    }

    protected function getMockRegionModel(array $data)
    {
        $iteration = 0;

        $model = $this->createPartialMock('\Mage_Directory_Model_Region', ['getIdField', 'getData', 'getId']);

        $model->expects($this->at($iteration++))
            ->method('getData')
            ->with('country_id')
            ->will($this->returnValue($data['country_id']));

        $model->expects($this->at($iteration++))
            ->method('getData')
            ->with('name')
            ->will($this->returnValue($data['name']));

        $model->expects($this->at($iteration))
            ->method('getId')
            ->will($this->returnValue($data['id']));

        return $model;
    }
}
