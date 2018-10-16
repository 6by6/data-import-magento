<?php

namespace SixBySix\Port\ValueConverter;

use Port\Exception\UnexpectedTypeException;

/**
 * Class Ucwords.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Ucwords
{
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

        return ucwords($input);
    }
}
