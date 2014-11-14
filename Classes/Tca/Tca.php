<?php
namespace TYPO3\CMS\Vidi\Tca;

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
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Exception\NotExistingClassException;

/**
 * A class to handle TCA ctrl.
 */
class Tca implements SingletonInterface, TcaServiceInterface {

	/**
	 * Fields that are considered as system.
	 *
	 * @var array
	 */
	static protected $systemFields = array(
		'uid',
		'pid',
		'tstamp',
		'crdate',
		'deleted',
		'hidden',
		'starttime',
		'endtime',
		'sys_language_uid',
		'l18n_parent',
		'l18n_diffsource',
		't3ver_oid',
		't3ver_id',
		't3ver_wsid',
		't3ver_label',
		't3ver_state',
		't3ver_stage',
		't3ver_count',
		't3ver_tstamp',
		't3_origuid',
	);

	/**
	 * @var array
	 */
	static protected $instances;

	/**
	 * Returns a class instance of a corresponding TCA service.
	 * If the class instance does not exist, create one.
	 *
	 * @throws NotExistingClassException
	 * @param string $dataType
	 * @param string $serviceType of the TCA, TcaServiceInterface::TYPE_TABLE or TcaServiceInterface::TYPE_GRID
	 * @return TcaServiceInterface
	 */
	static protected function getService($dataType = '', $serviceType) {
		if (TYPO3_MODE == 'BE' && empty($dataType)) {

			/** @var \TYPO3\CMS\Vidi\Module\ModuleLoader $moduleLoader */
			$moduleLoader = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
			$dataType = $moduleLoader->getDataType();
		}

		if (empty(self::$instances[$dataType][$serviceType])) {
			$className = sprintf('TYPO3\CMS\Vidi\Tca\%sService', ucfirst($serviceType));

			// Signal to pre-process the TCA of the given $dataType.
			self::emitPreProcessTcaSignal($dataType, $serviceType);

			$instance = GeneralUtility::makeInstance($className, $dataType, $serviceType);
			self::$instances[$dataType][$serviceType] = $instance;
		}
		return self::$instances[$dataType][$serviceType];
	}

	/**
	 * Returns a "grid" service instance.
	 *
	 * @param string|Content $tableNameOrContentObject
	 * @return \TYPO3\CMS\Vidi\Tca\GridService
	 */
	static public function grid($tableNameOrContentObject = '') {
		$tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;
		return self::getService($tableName, self::TYPE_GRID);
	}

	/**
	 * Returns a "table" service instance ("ctrl" part of the TCA).
	 *
	 * @param string|Content $tableNameOrContentObject
	 * @return \TYPO3\CMS\Vidi\Tca\TableService
	 */
	static public function table($tableNameOrContentObject = '') {
		$tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;
		return self::getService($tableName, self::TYPE_TABLE);
	}

	/**
	 * @return array
	 */
	public static function getInstanceStorage() {
		return self::$instances;
	}

	/**
	 * @return array
	 */
	public static function getSystemFields() {
		return self::$systemFields;
	}

	/**
	 * Signal that is called after the content repository for a content type has been instantiated.
	 *
	 * @signal
	 * @param string $dataType
	 * @param string $serviceType
	 * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
	 * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
	 */
	static protected function emitPreProcessTcaSignal($dataType, $serviceType) {
		self::getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\Tca\Tca', 'preProcessTca', array($dataType, $serviceType));
	}

	/**
	 * Get the SignalSlot dispatcher
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	static protected function getSignalSlotDispatcher() {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		return $objectManager->get('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
	}

}
