<?php

namespace SixBySix\Port\Exception;

use Exception;
use Port\Exception as PortException;

/**
 * Exception thrown when attribute code does not exist in Magento.
 *
 * @author SixBySix <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class AttributeNotExistException extends Exception implements PortException
{
    /**
     * @param string $attributeCode
     */
    public function __construct($attributeCode)
    {
        $message = sprintf('Attribute with code: "%s" does not exist', $attributeCode);
        parent::__construct($message);
    }
}
