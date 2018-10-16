<?php

namespace SixBySix\Port\ValueConverter;

use Ddeboer\DataImport\Exception\UnexpectedTypeException;
use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;

/**
 * Class UcwordsValueConverter
 * @package SixBySix\Port\ValueConverter
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UcwordsValueConverter implements ValueConverterInterface
{

    /**
     * @param string $input
     * @return string
     * @throws UnexpectedTypeException
     */
    public function convert($input)
    {
        if (!is_string($input)) {
            throw new UnexpectedTypeException($input, 'string');
        }

        return ucwords($input);
    }
}
