<?php

namespace Fab\Vidi\ViewHelpers\Result;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Domain\Model\Content;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper for rendering an XML export request.
 */
class ToXmlViewHelper extends AbstractToFormatViewHelper
{
    /**
     * Render an XML export.
     */
    public function render()
    {
        $objects = $this->templateVariableContainer->get('objects');

        // Make sure we have something to process...
        if (!empty($objects)) {
            // Initialization step.
            $this->initializeEnvironment($objects);
            $this->exportFileNameAndPath .= '.xml'; // add extension to the file.

            // Write the exported data to a XML file.
            $this->writeXmlFile($objects);

            // We must generate a zip archive since there are files included.
            if ($this->hasCollectedFiles()) {
                $this->writeZipFile();
                $response = $this->getZipResponse();
            } else {
                $response = $this->getXmlResponse($this->exportFileNameAndPath);
            }

            GeneralUtility::rmdir($this->temporaryDirectory, true);

            $this->sendRepsonse($response);
        }
    }

    /**
     * Write the XML file to a temporary location.
     *
     * @param array $objects
     * @return void
     */
    protected function writeXmlFile(array $objects)
    {
        // Get first object of $objects to check whether it contains possible files to include.
        /** @var Content $object */
        $object = reset($objects);
        $this->checkWhetherObjectMayIncludeFiles($object);

        $items = [];
        foreach ($objects as $object) {
            if ($this->hasFileFields()) {
                $this->collectFiles($object);
            }
            $items[] = $object->toValues();
        }

        $xml = new \SimpleXMLElement('<items/>');
        $xml = $this->arrayToXml($items, $xml);
        file_put_contents($this->exportFileNameAndPath, $this->formatXml($xml->asXML()));
    }

    /*
     * Convert an array to xml
     *
     * @return \SimpleXMLElement
     */
    protected function arrayToXml($array, \SimpleXMLElement $xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $key = is_numeric($key) ? 'item' : $key;
                $subNode = $xml->addChild($key);
                $this->arrayToXml($value, $subNode);
            } else {
                $key = is_numeric($key) ? 'item' : $key;
                $xml->addChild($key, "$value");
            }
        }
        return $xml;
    }

    /*
     * Format the XML so that is looks human friendly.
     *
     * @param string $xml
     * @return string
     */
    protected function formatXml($xml)
    {
        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);
        return $dom->saveXML();
    }

    /**
     * @return Response
     */
    protected function getXmlResponse(string $fileNameAndPath)
    {
        return $this->getFileResponse($fileNameAndPath)->withHeader('Content-Type', 'application/xml');
    }
}
