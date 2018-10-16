<?php

namespace SixBySix\Port\ValueConverter;

use Port\Exception\UnexpectedTypeException;

/**
 * Class Strtolower.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Strtolower
{
    public function __invoke($input)
    {
        return $this->convert($input);
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

        return strtolower($input);
    }
}
