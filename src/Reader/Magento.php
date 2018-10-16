<?php

namespace SixBySix\Port\Reader;

use Port\Reader;

/**
 * Magento will read a Magento 1.x collection.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Magento implements Reader
{
    /**
     * @var \Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    private $collection;

    /**
     * @var \Zend_Db_Statement_Interface
     */
    private $statement;

    /**
     * @var array Current Row
     */
    private $data = [];

    /**
     * @var int
     */
    private $current = 0;

    /**
     * @var null|int
     */
    private $count;

    /**
     * @var null|\Varien_Db_Select
     */
    private $select;

    /**
     * @param \Mage_Core_Model_Resource_Db_Collection_Abstract $collection
     */
    public function __construct(
        \Mage_Core_Model_Resource_Db_Collection_Abstract $collection
    ) {
        $this->collection = $collection;

        //get SQL statement
        $this->select = $this->collection->getSelect();
    }

    /**
     * Get the next row of data and store it.
     *
     * @return array
     */
    public function current()
    {
        return $this->data;
    }

    /**
     * Fetch next row and advance the current row index.
     */
    public function next()
    {
        $this->data = $this->statement->fetch();
        ++$this->current;
    }

    /**
     * Return current position.
     *
     * @return int
     */
    public function key()
    {
        return $this->current;
    }

    /**
     * Check whether the current index is not greater
     * then the size of the collection.
     *
     * @return bool
     */
    public function valid()
    {
        /*
         * Make sure $this->data is not false.
         *
         * A wierd bug with Magento getSize() on a collection
         * using joins & GROUP BY. COUNT(DISTINCT(idfield))
         * seems to fix it but is hard to patch. This simple check should return false
         * if the row if null
         */
        return (bool) $this->data;
    }

    /**
     * Rewind to the first element.
     */
    public function rewind()
    {
        $this->statement = $this->select->query();
        $this->current = 0;
        $this->next();
    }

    /**
     * Get the field (column, property) names.
     *
     * @throws \Zend_Db_Statement_Exception
     *
     * @return array
     */
    public function getFields()
    {
        $data = [];

        if (empty($this->data)) {
            $data = $this->select->query()->fetch();
        }

        return array_keys($data);
    }

    /**
     * Magento creates a COUNT(*) query for us.
     *
     * @return int
     */
    public function count()
    {
        if (null === $this->count) {
            $this->count = $this->collection->getSize();
        }

        return $this->count;
    }
}
