<?php

namespace SixBySix\Port\Reader;

use AydinHassan\XmlFuse\XmlFuse;
use InvalidArgumentException;
use Port\Exception\ReaderException;
use Port\Reader\ArrayReader;
use UnexpectedValueException;

/**
 * Read XML file.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class Xml extends ArrayReader
{
    /**
     * @param array  $stream
     * @param array  $xPaths
     * @param string $type
     */
    public function __construct($stream, array $xPaths = [], $type = 'nest')
    {
        if (!\is_resource($stream) || 'stream' !== get_resource_type($stream)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected argument to be a stream resource, got "%s"',
                    \is_object($stream) ? \get_class($stream) : \gettype($stream)
                )
            );
        }

        $xml = stream_get_contents($stream);

        try {
            $parser = XmlFuse::factory($type, $xml, $xPaths);
        } catch (UnexpectedValueException $e) {
            throw new ReaderException($e->getMessage(), 0, $e);
        }

        parent::__construct($parser->parse());
    }

    public function getFields()
    {
        if ($this->count() > 0) {
            return array_keys($this[0]);
        }

        return [];
    }
}
