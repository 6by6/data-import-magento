<?php

namespace SixBySix\Port;

use Port\Exception\UnexpectedTypeException;
use Port\Steps\StepAggregator as Workflow;

/**
 * Class NestedValueConverterWorkflow.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class NestedValueConverterWorkflow extends Workflow
{
    private $valueConverters = [];
    private $itemConverters = [];

    /**
     * Add a value converter to the workflow.
     *
     * @param array|string $field     Field
     * @param \Closure     $converter ValueConverter
     *
     * @return $this
     */
    public function addValueConverter($field, \Closure $converter)
    {
        //allow to attach the same converter to multiple fields in one go
        if (\is_array($field)) {
            $fields = $field;
            foreach ($fields as $field) {
                $this->valueConverters[$field][] = $converter;
            }
        } else {
            $this->valueConverters[$field][] = $converter;
        }

        return $this;
    }

    /**
     * Convert the item.
     *
     * @param string $item Original item values
     *
     * @throws UnexpectedTypeException
     *
     * @return array Converted item values
     */
    protected function convertItem($item)
    {
        foreach ($this->itemConverters as $converter) {
            $item = $converter($item);
            if ($item && !(\is_array($item) || ($item instanceof \ArrayAccess && $item instanceof \Traversable))) {
                throw new UnexpectedTypeException($item, 'false or array');
            }

            if (!$item) {
                return $item;
            }
        }

        if ($item && !(\is_array($item) || ($item instanceof \ArrayAccess && $item instanceof \Traversable))) {
            throw new UnexpectedTypeException($item, 'false or array');
        }

        foreach ($this->valueConverters as $property => $converters) {
            //is this is targeting a nested field
            if (false !== strpos($property, '/')) {
                $properties = explode('/', $property);
                $item = $this->recursivelyConvertValues($properties, $item, $converters);
            } else {
                $item = $this->recursivelyConvertValues([$property], $item, $converters);
            }
        }

        return $item;
    }

    /**
     * Recursively run value converters on nested data.
     *
     * @param array $properties
     * @param array $data
     * @param array $converters
     *
     * @return array
     */
    protected function recursivelyConvertValues(array $properties, array $data, array $converters)
    {
        $property = array_shift($properties);
        $isCollection = false;
        if ('[]' === substr($property, -2)) {
            $isCollection = true;
            $property = substr($property, 0, -2);
        }

        if (!\count($properties)) {
            //this is the deepest field

            //apply to all properties
            if ('*' === $property) {
                $data = array_map(function ($value) use ($converters) {
                    foreach ($converters as $converter) {
                        $value = $converter($value);
                    }

                    return $value;
                }, $data);
            } elseif (isset($data[$property]) || array_key_exists($property, $data)) {
                //This is an associative array
                foreach ($converters as $converter) {
                    $data[$property] = $converter($data[$property]);
                }
            }
        } else {
            if (isset($data[$property])) {
                if ($isCollection) {
                    foreach ($data[$property] as $key => $item) {
                        $data[$property][$key] = $this->recursivelyConvertValues($properties, $item, $converters);
                    }
                } else {
                    $data[$property] = $this->recursivelyConvertValues($properties, $data[$property], $converters);
                }
            }
        }

        return $data;
    }
}
