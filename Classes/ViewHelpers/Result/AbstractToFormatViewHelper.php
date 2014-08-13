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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Service\FileReferenceService;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Abstract View helper for rendering an Export request.
 */
abstract class AbstractToFormatViewHelper extends AbstractViewHelper {

	/**
	 * Store fields of type "file".
	 *
	 * @var array
	 */
	protected $fileTypeProperties = array();

	/**
	 * @var File[]
	 */
	protected $collectedFiles = array();

	/**
	 * @var string
	 */
	protected $exportFileNameAndPath;

	/**
	 * @var string
	 */
	protected $zipFileNameAndPath;

	/**
	 * @var string
	 */
	protected $temporaryDirectory;


	/**
	 * Write the zip file to a temporary location.
	 *
	 * @return void
	 */
	protected function writeZipFile() {

		$zip = new \ZipArchive();
		$zip->open($this->zipFileNameAndPath, \ZipArchive::CREATE);

		// Add the CSV content into the zipball.
		$zip->addFile($this->exportFileNameAndPath, basename($this->exportFileNameAndPath));

		// Add the files into the zipball.
		foreach ($this->collectedFiles as $file) {
			$zip->addFile($file->getForLocalProcessing(FALSE), $file->getIdentifier());
		}

		$zip->close();
	}

	/**
	 * Initialize some properties
	 *
	 * @param array $objects
	 * @return void
	 */
	protected function initializeEnvironment(array $objects) {

		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $object */
		$object = reset($objects);

		$this->temporaryDirectory = PATH_site . 'typo3temp/' . uniqid() . '/';
		GeneralUtility::mkdir($this->temporaryDirectory);

		// Compute file name and path variable
		$this->exportFileNameAndPath = $this->temporaryDirectory . $object->getDataType() . '-' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']);

		// Compute file name and path variable for zip
		$zipFileName = $object->getDataType() . '-' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']) . '.zip';
		$this->zipFileNameAndPath = $this->temporaryDirectory . $zipFileName;
	}

	/**
	 * Fetch the files given an object.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @return void
	 */
	protected function collectFiles(Content $object) {
		foreach ($this->fileTypeProperties as $property) {
			$files = FileReferenceService::getInstance()->findReferencedBy($property, $object);
			foreach ($files as $file) {
				$this->collectedFiles[$file->getUid()] = $file;
			}
		}
	}

	/**
	 * Tells whether the object has fields containing files.
	 *
	 * @return boolean
	 */
	protected function hasCollectedFiles() {
		return !empty($this->collectedFiles);
	}

	/**
	 * Tells whether the object has fields containing files.
	 *
	 * @return boolean
	 */
	protected function hasFileFields() {
		return !empty($this->fileTypeProperties);
	}

	/**
	 * Check whether the given object is meant to include files in some fields.
	 *
	 * @param Content $object
	 * @return void
	 */
	protected function checkWhetherObjectMayIncludeFiles(Content $object) {
		if (TcaService::grid($object->getDataType())->areFilesIncludedInExport()) {
			foreach ($object->toFields() as $fieldName) {
				$fieldType = TcaService::table($object->getDataType())->field($fieldName)->getType();

				if ($fieldType === TcaService::FILE) {
					$this->fileTypeProperties[] = GeneralUtility::camelCaseToLowerCaseUnderscored($fieldName);
				}
			}
		}
	}

	/**
	 * @return void
	 */
	protected function sendZipHttpHeaders() {

		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Response $response */
		$response = $this->templateVariableContainer->get('response');
		$response->setHeader('Pragma', 'public');
		$response->setHeader('Expires', '0');
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
		$response->setHeader('Content-Type', 'application/zip');
		$response->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->zipFileNameAndPath)  . '"');
		$response->setHeader('Content-Length', filesize($this->zipFileNameAndPath));
		$response->setHeader('Content-Description', 'File Transfer');
		$response->setHeader('Content-Transfer-Encoding', 'binary');

		$response->sendHeaders();
	}

	/**
	 * @return \TYPO3\CMS\Vidi\ViewHelpers\Grid\RowsViewHelper
	 */
	protected function getRowsViewHelper() {
		return $this->objectManager->get('TYPO3\CMS\Vidi\ViewHelpers\Grid\RowsViewHelper');
	}

	/**
	 * Returns a pointer to the database.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}
