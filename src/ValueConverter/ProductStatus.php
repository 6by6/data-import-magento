<?php

namespace SixBySix\Port\ValueConverter;

use Port\Exception\UnexpectedValueException;

/**
 * Class ProductStatus.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ProductStatus
{
    /**
     * @var array
     */
    private $productStatuses;

    /**
     * @var string
     */
    private $default = 'Disabled';

    /**
     *  Get the Tax Classes.
     */
    public function __construct()
    {
        $this->productStatuses = \Mage_Catalog_Model_Product_Status::getOptionArray();
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function convert($input)
    {
        if (empty($input)) {
            $input = $this->default;
        }

        if (!\in_array($input, $this->productStatuses, true)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Given Product Status: "%s" is not valid. Allowed values: "%s"',
                    $input,
                    implode('", "', $this->productStatuses)
                )
            );
        }

        return array_search($input, $this->productStatuses, true);
    }
}
