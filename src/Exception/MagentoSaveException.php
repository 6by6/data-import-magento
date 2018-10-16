<?php

namespace SixBySix\Port\Exception;

use Exception;
use Port\Exception as PortException;

/**
 * Exception thrown when error occurs saving entity.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MagentoSaveException extends Exception implements PortException
{
}
