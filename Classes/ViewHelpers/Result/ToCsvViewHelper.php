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
 * View helper for rendering a CSV export request.
 */
class ToCsvViewHelper extends AbstractToFormatViewHelper {

	/**
	 * Render a CSV export request.
	 *
	 * @return boolean
	 */
	public function render() {

		$objects = $this->templateVariableContainer->get('objects');

		// Make sure we have something to process...
		if (!empty($objects)) {

			// Initialization step.
			$this->initializeEnvironment($objects);
			$this->exportFileNameAndPath .= '.csv'; // add extension to the file.

			// Write the exported data to a CSV file.
			$this->writeCsvFile($objects);

			// We must generate a zip archive since there are files included.
			if ($this->hasCollectedFiles()) {

				$this->writeZipFile($objects);
				$this->sendZipHttpHeaders();

				readfile($this->zipFileNameAndPath);
			} else {
				$this->sendCsvHttpHeaders();
				readfile($this->exportFileNameAndPath);
			}

			GeneralUtility::rmdir($this->temporaryDirectory, TRUE);
		}
	}


	/**
	 * Write the CSV file to a temporary location.
	 *
	 * @param array $objects
	 * @return void
	 */
	protected function writeCsvFile(array $objects) {

		// Create a file pointer
		$output = fopen($this->exportFileNameAndPath, 'w');

		// Handle CSV header, get the first object and get the list of fields.
		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $object */
		$object = reset($objects);
		fputcsv($output, $object->toFields());
		$this->checkWhetherObjectMayIncludeFiles($object);

		foreach ($objects as $object) {
			if ($this->hasFileFields()) {
				$this->collectFiles($object);
			}

			// Make sure we have a flat array of values for the CSV purpose.
			$flattenValues = array();
			foreach ($object->toValues() as $fieldName => $value) {
				if (is_array($value)) {
					$flattenValues[$fieldName] = implode(', ', $value);
				} else {
					$flattenValues[$fieldName] = str_replace("\n", "\r", $value); // for Excel purpose.
				}
			}

			fputcsv($output, $flattenValues);
		}

		// close file handler
		fclose($output);
	}

	/**
	 * @return void
	 */
	protected function sendCsvHttpHeaders() {

		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
		$response = $this->templateVariableContainer->get('response');
		$response->setHeader('Content-Type', 'application/csv');
		$response->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->exportFileNameAndPath) . '"');
		$response->setHeader('Content-Length', filesize($this->exportFileNameAndPath));
		$response->setHeader('Content-Description', 'File Transfer');

		$response->sendHeaders();
	}

}
