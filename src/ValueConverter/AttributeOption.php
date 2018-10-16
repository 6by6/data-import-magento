<?php

namespace SixBySix\Port\ValueConverter;

use Port\Exception\UnexpectedValueException;
use SixBySix\Port\Options\OptionsParseTrait;

/**
 * Load the real Option Label for a given ID.
 *
 * @@author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class AttributeOption
{
    use OptionsParseTrait;

    /**
     * @var null|string
     */
    protected $attributeCode;

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
     * @param mixed $attributeCode
     * @param mixed $attributeOptions
     */
    public function __construct($attributeCode, $attributeOptions = [], $options = [])
    {
        $this->attributeCode = $attributeCode;
        $this->attributeOptions = $attributeOptions;
        $this->options = $this->parseOptions($this->options, $options);
    }

    /**
     * @param mixed $input
     *
     * @throws UnexpectedValueException
     *
     * @return mixed
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
            }

            return '';
        }
        //look up the real option value
        return $this->attributeOptions[$input];
    }
}
