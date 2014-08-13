<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Result;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper for rendering an XML export request.
 */
class ToXmlViewHelper extends AbstractToFormatViewHelper {

	/**
	 * Render an XML export.
	 *
	 * @return boolean
	 */
	public function render() {

		$objects = $this->templateVariableContainer->get('objects');

		// Make sure we have something to process...
		if (!empty($objects)) {

			// Initialization step.
			$this->initializeEnvironment($objects);
			$this->exportFileNameAndPath .= '.xml'; // add extension to the file.

			// Write the exported data to a CSV file.
			$this->writeXmlFile($objects);

			// We must generate a zip archive since there are files included.
			if ($this->hasCollectedFiles()) {

				$this->writeZipFile($objects);
				$this->sendZipHttpHeaders();

				readfile($this->zipFileNameAndPath);
			} else {
				$this->sendXmlHttpHeaders();
				readfile($this->exportFileNameAndPath);
			}

			GeneralUtility::rmdir($this->temporaryDirectory, TRUE);
		}
	}


	/**
	 * Write the XML file to a temporary location.
	 *
	 * @param array $objects
	 * @return void
	 */
	protected function writeXmlFile(array $objects) {

		// Handle CSV header, get first object of $objects
		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $object */
		$object = reset($objects);
		$this->checkWhetherObjectMayIncludeFiles($object);

		$items = array();
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
	 * @return void
	 */
	protected function arrayToXml($array, \SimpleXMLElement $xml) {
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
	protected function formatXml($xml) {
		$dom = new \DOMDocument("1.0");
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml);
		return $dom->saveXML();
	}

	/**
	 * @return void
	 */
	protected function sendXmlHttpHeaders() {

		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
		$response = $this->templateVariableContainer->get('response');
		$response->setHeader('Content-Type', 'application/xml');
		$response->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->exportFileNameAndPath) . '"');
		$response->setHeader('Content-Length', filesize($this->exportFileNameAndPath));
		$response->setHeader('Content-Description', 'File Transfer');

		$response->sendHeaders();
	}

}
