<?php

namespace SixBySix\Port\ValueConverter;

use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;
use Ddeboer\DataImport\Exception\UnexpectedValueException;
use SixBySix\Port\Options\OptionsParseTrait;

/**
 * Load the real Option Label for a given ID
 *
 * Class AttributeOptionValueConverter
 * @package SixBySix\Port\ValueConverter
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class AttributeOptionValueConverter implements ValueConverterInterface
{
    use OptionsParseTrait;

    /**
     * @var string|null
     */
    protected $attributeCode = null;

    /**
     * @var array
     */
    protected $options = [
        'returnEmptyStringIfOptionNotExist' => false,
    ];

    /**
     * @var array
     */
    protected $attributeOptions = [];

    /**
     * @param array $options
     */
    public function __construct($attributeCode, $attributeOptions = [], $options = [])
    {
        $this->attributeCode    = $attributeCode;
        $this->attributeOptions = $attributeOptions;
        $this->options          = $this->parseOptions($this->options, $options);
    }

    /**
     * @param mixed $input
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function convert($input)
    {
        if (!array_key_exists($input, $this->attributeOptions)) {
            if (!$this->options['returnEmptyStringIfOptionNotExist']) {
                throw new UnexpectedValueException(
                    sprintf(
                        '"%s" does not appear to be a valid attribute option for "%s"',
                        $input,
                        $this->attributeCode
                    )
                );
            } else {
                return '';
            }
        }
        //look up the real option value
        return $this->attributeOptions[$input];
    }
}
