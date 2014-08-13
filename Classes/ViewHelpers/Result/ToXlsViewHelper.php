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
use TYPO3\CMS\Vidi\Service\SpreadSheetService;

/**
 * View helper for rendering a CSV export request.
 */
class ToXlsViewHelper extends AbstractToFormatViewHelper {

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
			$this->exportFileNameAndPath .= '.xls'; // add extension to the file.

			// Write the exported data to a CSV file.
			$this->writeXlsFile($objects);

			// We must generate a zip archive since there are files included.
			if ($this->hasCollectedFiles()) {

				$this->writeZipFile($objects);
				$this->sendZipHttpHeaders();

				readfile($this->zipFileNameAndPath);
			} else {
				$this->sendXlsHttpHeaders();
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
	protected function writeXlsFile(array $objects) {

		/** @var SpreadSheetService $spreadSheet */
		$spreadSheet = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Service\SpreadSheetService');

		// Handle object header, get the first object and get the list of fields.
		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $object */
		$object = reset($objects);
		$spreadSheet->addRow($object->toFields());

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
					$flattenValues[$fieldName] = $value;
				}
			}

			$spreadSheet->addRow($flattenValues);
		}

		file_put_contents($this->exportFileNameAndPath, $spreadSheet->toString());
	}

	/**
	 * @return void
	 */
	protected function sendXlsHttpHeaders() {

		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
		$response = $this->templateVariableContainer->get('response');
		$response->setHeader('Pragma', 'public');
		$response->setHeader('Expires', '0');
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$response->setHeader('Content-Type', 'application/vnd.ms-excel');
		$response->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->exportFileNameAndPath) . '"');
		$response->setHeader('Content-Length', filesize($this->exportFileNameAndPath));
		$response->setHeader('Content-Description', 'File Transfer');
		$response->setHeader('Content-Transfer-Encoding', 'binary');

		$response->sendHeaders();
	}

}
