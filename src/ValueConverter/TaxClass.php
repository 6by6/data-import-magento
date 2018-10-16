<?php

namespace SixBySix\Port\ValueConverter;

use Port\Exception\UnexpectedValueException;

/**
 * Class TaxClass.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class TaxClass
{
    /**
     * @var array
     */
    private $taxClasses = [];

    /**
     * @var string
     */
    private $default = 'Taxable Goods';

    /**
     *  Get the Tax Classes.
     */
    public function __construct()
    {
        $model = \Mage::getSingleton('tax/class_source_product');
        $productTaxClassOptions = $model->getAllOptions();
        foreach ($productTaxClassOptions as $option) {
            $this->taxClasses[$option['value']] = $option['label'];
        }
    }

    public function __invoke($input)
    {
        return $this->convert($input);
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

        if (!\in_array($input, $this->taxClasses, true)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Given Tax-Class: "%s" is not valid. Allowed values: "%s"',
                    $input,
                    implode('", "', $this->taxClasses)
                )
            );
        }

        return array_search($input, $this->taxClasses, true);
    }
}
