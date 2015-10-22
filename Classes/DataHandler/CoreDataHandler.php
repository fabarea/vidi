<?php
namespace Fab\Vidi\DataHandler;

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
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Tca\Tca;

/**
 * Data Handler which wraps the Core Data Handler
 */
class CoreDataHandler extends AbstractDataHandler {

	/**
	 * @var array
	 */
	protected $dataHandler;

	/**
	 * Process Content with action "update".
	 *
	 * @param Content $content
	 * @throws \Exception
	 * @return bool
	 */
	public function processUpdate(Content $content) {

		$values = array();

		// Check the field to be updated exists
		foreach ($content->toArray() as $fieldName => $value) {
			if (!Tca::table($content->getDataType())->hasField($fieldName)) {
				$message = sprintf('It looks field "%s" does not exist for data type "%s"', $fieldName, $content->getDataType());
				throw new \Exception($message, 1390668497);
			}

			// Flatten value if array given which is required for the DataHandler.
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			$values[$fieldName] = $value;
		}

		$data[$content->getDataType()][$content->getUid()] = $values;

		$dataHandler = $this->getDataHandler();
		$dataHandler->start($data, array());
		$dataHandler->process_datamap();
		$this->errorMessages = $dataHandler->errorLog;

		// Returns TRUE is log does not contain errors.
		return empty($dataHandler->errorLog);
	}

	/**
	 * Process Content with action "remove".
	 *
	 * @param Content $content
	 * @return bool
	 */
	public function processRemove(Content $content) {

		// Build command
		$cmd[$content->getDataType()][$content->getUid()]['delete'] = 1;

		/** @var $dataHandler \TYPO3\CMS\Core\DataHandling\DataHandler */
		$dataHandler = $this->getDataHandler();
		$dataHandler->start(array(), $cmd);
		$dataHandler->process_datamap();
		$dataHandler->process_cmdmap();
		$this->errorMessages = $dataHandler->errorLog;

		// Returns TRUE is log does not contain errors.
		return empty($dataHandler->errorLog);
	}

	/**
	 * Process Content with action "copy".
	 *
	 * @param Content $content
	 * @param string $target
	 * @return bool
	 */
	public function processCopy(Content $content, $target) {
		// TODO: Implement processCopy() method.
	}

	/**
	 * Process Content with action "move".
	 * The $target corresponds to the pid to move the records to.
	 * It can also be a negative value in case of sorting. The negative value would be the uid of its predecessor.
	 *
	 * @param Content $content
	 * @param int $target corresponds
	 * @return bool
	 */
	public function processMove(Content $content, $target) {

		// Build command
		$cmd[$content->getDataType()][$content->getUid()]['move'] = $target;

		/** @var $dataHandler \TYPO3\CMS\Core\DataHandling\DataHandler */
		$dataHandler = $this->getDataHandler();
		$dataHandler->start(array(), $cmd);
		$dataHandler->process_datamap();
		$dataHandler->process_cmdmap();
		$this->errorMessages = $dataHandler->errorLog;

		// Returns TRUE is log does not contain errors.
		return empty($dataHandler->errorLog);
	}

	/**
	 * Process Content with action "localize".
	 *
	 * @param Content $content
	 * @param int $language
	 * @return bool
	 */
	public function processLocalize(Content $content, $language) {

		$command[$content->getDataType()][$content->getUid()]['localize'] = $language;

		$dataHandler = $this->getDataHandler();
		$dataHandler->start(array(), $command);
		$dataHandler->process_datamap();
		$dataHandler->process_cmdmap();
		$this->errorMessages = $dataHandler->errorLog;

		// Returns TRUE is log does not contain errors.
		return empty($dataHandler->errorLog);
	}

	/**
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected function getDataHandler() {
		if (!$this->dataHandler) {
			$this->dataHandler = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		}
		return $this->dataHandler;
	}

}
