<?php

namespace SixBySix\Port\Reader;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @internal
 * @coversNothing
 */
final class MagentoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Magento
     */
    protected $reader;

    protected $collection;

    protected $select;

    protected function setUp()
    {
        $this->collection = $this
            ->getMockBuilder('\Mage_Core_Model_Resource_Db_Collection_Abstract')
            ->disableOriginalConstructor()
            ->getMock();

        $this->select = $this
            ->getMockBuilder('\Varien_Db_Select')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetFields()
    {
        $this->collection
            ->expects($this->once())
            ->method('getSelect')
            ->will($this->returnValue($this->select));

        $statement = $this->createMock('\Zend_Db_Statement_Interface');
        $this->select
            ->expects($this->once())
            ->method('query')
            ->will($this->returnValue($statement));

        $this->reader = new Magento($this->collection);

        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(['one' => 1, 'two' => 2, 'three' => 3]));

        $this->assertSame(['one', 'two', 'three'], $this->reader->getFields());
    }

    public function testGetCountReturnsCollectionSize()
    {
        $this->collection
            ->expects($this->once())
            ->method('getSelect')
            ->will($this->returnValue($this->select));

        $this->reader = new Magento($this->collection);

        $this->collection
            ->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(5));

        $this->assertSame(5, $this->reader->count());
    }

    public function testRewindGetsNewQueryAndIndexIsReset()
    {
        $this->collection
            ->expects($this->once())
            ->method('getSelect')
            ->will($this->returnValue($this->select));

        $statement1 = $this->createMock('\Zend_Db_Statement_Interface');
        $statement2 = $this->createMock('\Zend_Db_Statement_Interface');
        $this->select
            ->expects($this->at(0))
            ->method('query')
            ->will($this->returnValue($statement1));

        $this->select
            ->expects($this->at(1))
            ->method('query')
            ->will($this->returnValue($statement2));

        $this->reader = new Magento($this->collection);

        $statement1
            ->expects($this->exactly(2))
            ->method('fetch');

        $statement2
            ->expects($this->once())
            ->method('fetch');

        $this->reader->rewind();
        $this->assertSame(1, $this->reader->key());
        $this->reader->next();
        $this->assertSame(2, $this->reader->key());
        $this->reader->rewind();
        $this->assertSame(1, $this->reader->key());
    }

    public function testIterator()
    {
        $this->collection
            ->expects($this->once())
            ->method('getSelect')
            ->will($this->returnValue($this->select));

        $this->reader = new Magento($this->collection);
        $statement = $this->createMock('\Zend_Db_Statement_Interface');
        $this->select
            ->expects($this->once())
            ->method('query')
            ->will($this->returnValue($statement));

        $data = [
            ['one' => 1, 'two' => 2, 'three' => 3],
            ['one' => 11, 'two' => 22, 'three' => 33],
            ['one' => 111, 'two' => 222, 'three' => 333],
        ];

        $statement->expects($this->at(0))
            ->method('fetch')
            ->will($this->returnValue($data[0]));

        $statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue($data[1]));

        $statement->expects($this->at(2))
            ->method('fetch')
            ->will($this->returnValue($data[2]));

        $i = 1;
        foreach ($this->reader as $key => $row) {
            $this->assertSame($i, $key);
            $this->assertSame($data[$i - 1], $row);
            ++$i;
        }
    }

    public function testReaderReturnsAllDataIfCollectionSizeIsWrong()
    {
        $this->collection
            ->expects($this->once())
            ->method('getSelect')
            ->will($this->returnValue($this->select));

        $this->reader = new Magento($this->collection);
        $statement = $this->createMock('\Zend_Db_Statement_Interface');
        $this->select
            ->expects($this->once())
            ->method('query')
            ->will($this->returnValue($statement));

        $data = [
            ['one' => 1, 'two' => 2, 'three' => 3],
            ['one' => 11, 'two' => 22, 'three' => 33],
            ['one' => 111, 'two' => 222, 'three' => 333],
        ];

        $statement->expects($this->at(0))
            ->method('fetch')
            ->will($this->returnValue($data[0]));

        $statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue($data[1]));

        $statement->expects($this->at(2))
            ->method('fetch')
            ->will($this->returnValue($data[2]));

        $i = 1;
        foreach ($this->reader as $key => $row) {
            $this->assertSame($i, $key);
            $this->assertSame($data[$i - 1], $row);
            ++$i;
        }
    }
}
