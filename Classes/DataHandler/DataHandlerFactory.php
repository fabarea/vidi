<?php
namespace TYPO3\CMS\Vidi\DataHandler;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Factory which will return an appropriate Data Handler.
 */
class DataHandlerFactory implements SingletonInterface {

	/**
	 * @var string
	 */
	protected $actionName = '';

	/**
	 * @var string
	 */
	protected $dataType = '';

	/**
	 * Default is CoreDataHandler which wraps the Core DataHandler.
	 *
	 * @var string
	 */
	protected $defaultDataHandler = 'TYPO3\CMS\Vidi\DataHandler\CoreDataHandler';

	/**
	 * @param string $actionName
	 * @return $this
	 */
	public function action($actionName) {
		$this->actionName = $actionName;
		return $this;
	}

	/**
	 * @param string $dataType
	 * @return $this
	 */
	public function forType($dataType) {
		$this->dataType = $dataType;
		return $this;
	}

	/**
	 * Returns a Data Handler instance.
	 *
	 * @throws \Exception
	 * @return DataHandlerInterface
	 */
	public function getDataHandler() {

		if (empty($this->dataType)) {
			throw new \Exception('Attribute $this->dataType can not be empty', 1410001035);
		}

		if (empty($this->actionName)) {
			throw new \Exception('Attribute $this->actionName can not be empty', 1410001036);
		}

		if (isset($GLOBALS['TCA'][$this->dataType]['vidi']['data_handler'][$this->actionName])) {
			$className = $GLOBALS['TCA'][$this->dataType]['vidi']['data_handler'][$this->actionName];
		} elseif (isset($GLOBALS['TCA'][$this->dataType]['vidi']['data_handler']['*'])) {
			$className = $GLOBALS['TCA'][$this->dataType]['vidi']['data_handler']['*'];
		} else {
			$className = $this->defaultDataHandler;
		}

		$dataHandler = GeneralUtility::makeInstance($className);
		return $dataHandler;
	}

}
