<?php

namespace SixBySix\PortTest\Service;

use SixBySix\Port\Service\AttributeService;

/**
 * Class AttributeServiceTest.
 *
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class AttributeServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var /Mage_Eav_Model_Entity_Attribute
     */
    protected $attrModel;

    /**
     * @var \Mage_Eav_Model_Entity_Attribute_Source_Table
     */
    protected $attrSrcModel;

    /**
     * @var AttributeService
     */
    protected $attributeService;

    protected function setUp()
    {
        $this->attrModel = $this->createMock('\Mage_Eav_Model_Entity_Attribute');
        $this->attrSrcModel = $this->createMock('\Mage_Eav_Model_Entity_Attribute_Source_Table');
        $this->attributeService = new AttributeService($this->attrModel, $this->attrSrcModel);
    }

    public function testGetAttributeCreatesAttributeOptionIfItDoesNotExist()
    {
        $attribute = $this->createMock('\Mage_Eav_Model_Entity_Attribute_Abstract');

        $options = [
            ['label' => 'option1', 'value' => 'code1'],
            ['label' => 'option2', 'value' => 'code2'],
        ];

        $this->attrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'code3')
            ->will($this->returnValue(1));

        $this->attrModel
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->will($this->returnValue($attribute));

        $this->attrSrcModel
            ->expects($this->exactly(2))
            ->method('setAttribute')
            ->with($attribute);

        $this->attrSrcModel
            ->expects($this->once())
            ->method('getAllOptions')
            ->with(false)
            ->will($this->returnValue($options));

        $this->attrSrcModel
            ->expects($this->once())
            ->method('getOptionId')
            ->with('option3')
            ->will($this->returnValue('code3'));

        $data = [
            'value' => [
                'option' => ['option3', 'option3'],
            ],
        ];

        $attribute
            ->expects($this->once())
            ->method('usesSource')
            ->will($this->returnValue(true));

        $attribute
            ->expects($this->once())
            ->method('setData')
            ->with('option', $data);

        $attribute
            ->expects($this->once())
            ->method('save');

        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'code3', 'option3');
        $this->assertSame($ret, 'code3');
    }

    public function testGetAttributeCreatesAttributeOptionIfItDoesNotExistAndCachesIt()
    {
        $attribute = $this->createMock('\Mage_Eav_Model_Entity_Attribute_Abstract');

        $options = [
            ['label' => 'option1', 'value' => 'code1'],
            ['label' => 'option2', 'value' => 'code2'],
        ];

        $this->attrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'code3')
            ->will($this->returnValue(1));

        $this->attrModel
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->will($this->returnValue($attribute));

        $this->attrSrcModel
            ->expects($this->exactly(2))
            ->method('setAttribute')
            ->with($attribute);

        $this->attrSrcModel
            ->expects($this->once())
            ->method('getAllOptions')
            ->with(false)
            ->will($this->returnValue($options));

        $this->attrSrcModel
            ->expects($this->once())
            ->method('getOptionId')
            ->with('option3')
            ->will($this->returnValue('code3'));

        $data = [
            'value' => [
                'option' => ['option3', 'option3'],
            ],
        ];

        $attribute
            ->expects($this->once())
            ->method('usesSource')
            ->will($this->returnValue(true));

        $attribute
            ->expects($this->once())
            ->method('setData')
            ->with('option', $data);

        $attribute
            ->expects($this->once())
            ->method('save');

        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'code3', 'option3');
        $this->assertSame($ret, 'code3');

        //pls load from cache
        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'code3', 'option3');
        $this->assertSame($ret, 'code3');
    }

    public function testGetAttributeReturnsIdIfItExists()
    {
        $attribute = $this->createMock('\Mage_Eav_Model_Entity_Attribute_Abstract');

        $options = [
            ['label' => 'option1', 'value' => 'code1'],
            ['label' => 'option2', 'value' => 'code2'],
        ];

        $this->attrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'code2')
            ->will($this->returnValue(1));

        $this->attrModel
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->will($this->returnValue($attribute));

        $this->attrSrcModel
            ->expects($this->once())
            ->method('setAttribute')
            ->with($attribute);

        $this->attrSrcModel
            ->expects($this->once())
            ->method('getAllOptions')
            ->with(false)
            ->will($this->returnValue($options));

        $attribute
            ->expects($this->once())
            ->method('usesSource')
            ->will($this->returnValue(true));

        $attribute
            ->expects($this->never())
            ->method('setData');

        $attribute
            ->expects($this->never())
            ->method('save');

        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'code2', 'option2');
        $this->assertSame($ret, 'code2');
    }

    public function testGetAttributeReturnsValueIfAttributeDoesNotUseSource()
    {
        $attribute = $this->createMock('\Mage_Eav_Model_Entity_Attribute_Abstract');

        $this->attrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'attribute_code')
            ->will($this->returnValue(1));

        $this->attrModel
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->will($this->returnValue($attribute));

        $attribute
            ->expects($this->once())
            ->method('usesSource')
            ->will($this->returnValue(false));

        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'attribute_code', 'some_value');
        $this->assertSame($ret, 'some_value');
    }

    public function testGetAttributeThrowsExceptionIfAttributeDoesNotExist()
    {
        $this->expectException(
            'SixBySix\Port\Exception\AttributeNotExistException'
        );
        $this->expectExceptionMessage(
            'Attribute with code: "not_here" does not exist'
        );

        $this->attrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'not_here')
            ->will($this->returnValue(false));

        $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'not_here', 'some_value');
    }

    public function testAllAttributeOptionsAreCached()
    {
        $attribute = $this->createMock('\Mage_Eav_Model_Entity_Attribute_Abstract');

        $options = [
            ['label' => 'option1', 'value' => 'code1'],
            ['label' => 'option2', 'value' => 'code2'],
        ];

        $this->attrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'code2')
            ->will($this->returnValue(1));

        $this->attrModel
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->will($this->returnValue($attribute));

        $this->attrSrcModel
            ->expects($this->once())
            ->method('setAttribute')
            ->with($attribute);

        $this->attrSrcModel
            ->expects($this->once())
            ->method('getAllOptions')
            ->with(false)
            ->will($this->returnValue($options));

        $attribute
            ->expects($this->once())
            ->method('usesSource')
            ->will($this->returnValue(true));

        $attribute
            ->expects($this->never())
            ->method('setData');

        $attribute
            ->expects($this->never())
            ->method('save');

        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'code2', 'option2');
        $this->assertSame($ret, 'code2');

        //retrieve via cache hopefully
        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'code2', 'option2');
        $this->assertSame($ret, 'code2');
    }

    public function testGetAttributeValueIdIsNotCaseSensitive()
    {
        $attribute = $this->createMock('\Mage_Eav_Model_Entity_Attribute_Abstract');

        $options = [
            ['label' => 'option1', 'value' => 'code1'],
            ['label' => 'option2', 'value' => 'code2'],
        ];

        $this->attrModel
            ->expects($this->once())
            ->method('getIdByCode')
            ->with('catalog_product', 'code2')
            ->will($this->returnValue(1));

        $this->attrModel
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->will($this->returnValue($attribute));

        $this->attrSrcModel
            ->expects($this->once())
            ->method('setAttribute')
            ->with($attribute);

        $this->attrSrcModel
            ->expects($this->once())
            ->method('getAllOptions')
            ->with(false)
            ->will($this->returnValue($options));

        $attribute
            ->expects($this->once())
            ->method('usesSource')
            ->will($this->returnValue(true));

        $attribute
            ->expects($this->never())
            ->method('setData');

        $attribute
            ->expects($this->never())
            ->method('save');

        $ret = $this->attributeService->getAttrCodeCreateIfNotExist('catalog_product', 'code2', 'OPTION2');
        $this->assertSame($ret, 'code2');
    }
}
