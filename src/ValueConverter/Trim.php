<?php

namespace SixBySix\Port\ValueConverter;

use Port\Exception\UnexpectedTypeException;

/**
 * Class Trim.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Trim
{
    /**
     * @var string
     */
    private $charMask;

    /**
     * @param $charMask
     */
    public function __construct($charMask = ' \t\n\r\0\x0B')
    {
        $this->charMask = $charMask;
    }

    /**
     * @param string $input
     *
     * @throws UnexpectedTypeException
     *
     * @return string
     */
    public function convert($input)
    {
        if (!\is_string($input)) {
            throw new UnexpectedTypeException($input, 'string');
        }

        return trim($input, $this->charMask);
    }
}
