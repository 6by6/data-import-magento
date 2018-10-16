<?php

namespace SixBySix\Port\ValueConverter;

use Ddeboer\DataImport\Exception\UnexpectedTypeException;
use Ddeboer\DataImport\Exception\UnexpectedValueException;
use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;

/**
 * Class ProductVisibilityValueConverter
 * @package SixBySix\Port\ValueConverter
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ProductVisibilityValueConverter implements ValueConverterInterface
{

    /**
     * @var array
     */
    private $productVisibilities = [];

    /**
     * @var string
     */
    private $default = 'Not Visible Individually';

    /**
     *  Get the Tax Classes
     */
    public function __construct()
    {
        $this->productVisibilities = \Mage_Catalog_Model_Product_Visibility::getOptionArray();
    }

    /**
     * @param string $input
     * @return string
     */
    public function convert($input)
    {
        if (empty($input)) {
            $input = $this->default;
        }

        if (!in_array($input, $this->productVisibilities)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Given Product Visibility: "%s" is not valid. Allowed values: "%s"',
                    $input,
                    implode('", "', $this->productVisibilities)
                )
            );
        }

        return array_search($input, $this->productVisibilities);
    }
}
