<?php

namespace SixBySix\Port\Writer;

use DOMDocument;
use DOMElement;
use Exception;
use Port\Writer;

/**
 * Class XmlWriter.
 *
 * @author Six By Six <hello@sixbysix.co.uk>
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class XmlWriter implements Writer
{
    /**
     * @var string
     */
    private $rootElement = 'root';

    /**
     * @var string
     */
    private $outputFileName;

    /**
     * @var array
     */
    private $arrayMappings;

    /**
     * @param string $outputFileName
     * @param array  $arrayMappings
     * @param string $rootElement
     */
    public function __construct($outputFileName, $arrayMappings = [], $rootElement = 'root')
    {
        $this->rootElement = $rootElement;
        $this->outputFileName = $outputFileName;
        $this->arrayMappings = $arrayMappings;
    }

    /**
     * Prepare the writer before writing the items.
     *
     * @return $this
     */
    public function prepare()
    {
    }

    /**
     * Write one data item.
     *
     * @param array $item The data item with converted values
     *
     * @throws Exception
     *
     * @return $this
     */
    public function writeItem(array $item)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement($this->rootElement);
        $dom->appendChild($root);

        $this->arrayToXml($root, $dom, $item);

        $res = $dom->save($this->outputFileName);

        if (false === $res) {
            throw new WriterException(sprintf('Could not write XML file to: "%s"', $this->outputFileName));
        }

        return $this;
    }

    /**
     * Wrap up the writer after all items have been written.
     *
     * @return $this
     */
    public function finish()
    {
    }

    /**
     * @param DOMElement  $root
     * @param DOMDocument $dom
     * @param array       $data
     * @param null|string $previousKey
     */
    private function arrayToXml(DOMElement $root, DOMDocument $dom, array $data, $previousKey = null)
    {
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                if (\is_string($previousKey) && isset($this->arrayMappings[$previousKey])) {
                    $nodeKey = $this->arrayMappings[$previousKey];
                } else {
                    $nodeKey = $key;
                }

                $node = $dom->createElement($nodeKey);
                $root->appendChild($node);
                $this->arrayToXml($node, $dom, $value, $key);
            } else {
                $node = $dom->createElement($key, $value);
                $root->appendChild($node);
            }
        }
    }
}
