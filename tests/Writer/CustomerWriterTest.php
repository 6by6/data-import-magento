<?php

namespace SixBySix\PortTest\Writer;

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
        $this->customerModel = $this->createMock('\Mage_Customer_Model_Customer');
        $this->customerWriter = new CustomerWriter($this->customerModel);
    }

    public function testMagentoModelIsSaved()
    {
        $data = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
        ];

        $this->customerModel
            ->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->customerModel
            ->expects($this->once())
            ->method('save');

        $this->customerModel
            ->expects($this->once())
            ->method('getPrimaryAddresses')
            ->will($this->returnValue([]));

        $this->customerModel
            ->expects($this->once())
            ->method('getAdditionalAddresses')
            ->will($this->returnValue([]));

        $this->customerWriter->writeItem($data);
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

        $name = $data['firstname'].' '.$data['lastname'];

        $customerData = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
        ];

        $this->customerModel
            ->expects($this->once())
            ->method('setData')
            ->with($customerData);

        $addressModel = $this->createMock('\Mage_Customer_Model_Address');

        $directoryResourceModel = $this->getMockBuilder('\Mage_Directory_Model_Resource_Region_Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $args = [$this->customerModel, $addressModel, $directoryResourceModel];
        $methods = ['lookUpRegion', 'processRegions'];
        $this->customerWriter = $this->getMock('SixBySix\Port\Writer\CustomerWriter', $methods, $args);

        /*$this->customerWriter
             ->expects($this->once())
             ->method('processRegions')
             ->with($directoryResourceModel)
             ->will($this->returnValue(array()));*/

        $this->customerWriter
            ->expects($this->once())
            ->method('lookUpRegion')
            ->with($addressData['region'], $addressData['country_id'], $name)
            ->will($this->returnValue(1));

        unset($addressData['region']);
        $addressData['region_id'] = 1;
        $addressModel
            ->expects($this->once())
            ->method('setData')
            ->with($addressData);

        $this->customerModel
            ->expects($this->once())
            ->method('addAddress')
            ->with($addressModel);

        $this->customerModel
            ->expects($this->once())
            ->method('save');

        $this->customerModel
            ->expects($this->once())
            ->method('getPrimaryAddresses')
            ->will($this->returnValue([]));

        $this->customerModel
            ->expects($this->once())
            ->method('getAdditionalAddresses')
            ->will($this->returnValue([]));

        $this->customerWriter->writeItem($data);
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

        $name = $data['firstname'].' '.$data['lastname'];

        $customerData = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
        ];

        $this->customerModel
            ->expects($this->once())
            ->method('setData')
            ->with($customerData);

        $addressModel = $this->createMock('\Mage_Customer_Model_Address');

        $directoryResourceModel = $this->getMockBuilder('\Mage_Directory_Model_Resource_Region_Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $args = [$this->customerModel, $addressModel, $directoryResourceModel];
        $methods = ['lookUpRegion', 'processRegions'];
        $this->customerWriter = $this->getMock('SixBySix\Port\Writer\CustomerWriter', $methods, $args);

        /*$this->customerWriter
             ->expects($this->once())
             ->method('processRegions')
             ->with($directoryResourceModel)
             ->will($this->returnValue(array()));*/

        $this->customerWriter
            ->expects($this->once())
            ->method('lookUpRegion')
            ->with($addressData['region'], $addressData['country_id'], $name)
            ->will($this->returnValue(false));

        $addressModel
            ->expects($this->once())
            ->method('setData')
            ->with($addressData);

        $this->customerModel
            ->expects($this->once())
            ->method('addAddress')
            ->with($addressModel);

        $this->customerModel
            ->expects($this->once())
            ->method('save');

        $this->customerModel
            ->expects($this->once())
            ->method('getPrimaryAddresses')
            ->will($this->returnValue([]));

        $this->customerModel
            ->expects($this->once())
            ->method('getAdditionalAddresses')
            ->will($this->returnValue([]));

        $this->customerWriter->writeItem($data);
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
        $data = [
            'firstname' => 'Aydin',
            'lastname' => 'Hassan',
            'email' => 'aydin@hotmail.co.uk',
        ];

        $this->customerModel
            ->expects($this->once())
            ->method('setData')
            ->with($data);

        $e = new \Mage_Customer_Exception('Save Failed');
        $this->customerModel
            ->expects($this->once())
            ->method('save')
            ->will($this->throwException($e));

        $this->customerModel
            ->expects($this->once())
            ->method('getPrimaryAddresses')
            ->will($this->returnValue([]));

        $this->customerModel
            ->expects($this->once())
            ->method('getAdditionalAddresses')
            ->will($this->returnValue([]));

        $this->expectException('SixBySix\Port\Exception\MagentoSaveException');
        $this->expectExceptionMessage('Save Failed');
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
