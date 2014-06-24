<?php
namespace TYPO3\CMS\Vidi\Tca;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Exception\NotExistingClassException;

/**
 * A class to handle TCA ctrl.
 */
class TcaService implements SingletonInterface, TcaServiceInterface {

	const TEXT = 'text';

	const NUMBER = 'number';

	const EMAIL = 'email';

	const DATE = 'date';

	const DATETIME = 'datetime';

	const TEXTAREA = 'textarea';

	const SELECT = 'select';

	const RADIO = 'radio';

	const CHECKBOX = 'check';

	const FILE = 'file';

	const MULTISELECT = 'multiselect';

	const TREE = 'tree';

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
	 * @param string $tableName
	 * @param string $serviceType of the TCA. Typical values are: field, table, grid
	 * @return TcaServiceInterface
	 */
	static public function getService($tableName = '', $serviceType) {
		if (TYPO3_MODE == 'BE' && empty($tableName)) {

			/** @var \TYPO3\CMS\Vidi\Module\ModuleLoader $moduleLoader */
			$moduleLoader = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
			$tableName = $moduleLoader->getDataType();
		}

		if (empty(self::$instances[$tableName][$serviceType])) {
			$className = sprintf('TYPO3\CMS\Vidi\Tca\%sService', ucfirst($serviceType));

			if (!class_exists($className)) {
				throw new NotExistingClassException('Class does not exit: ' . $className, 1357060937);

			}
			$instance = GeneralUtility::makeInstance($className, $tableName, $serviceType);
			self::$instances[$tableName][$serviceType] = $instance;
		}
		return self::$instances[$tableName][$serviceType];
	}

	/**
	 * Returns a class instance of a corresponding TCA service.
	 * This is a shorthand method for "grid".
	 *
	 * @param string|Content $tableNameOrContentObject
	 * @return \TYPO3\CMS\Vidi\Tca\GridService
	 */
	static public function grid($tableNameOrContentObject = '') {
		$tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;
		return self::getService($tableName, self::TYPE_GRID);
	}

	/**
	 * Returns a class instance of a corresponding TCA service.
	 * This is a shorthand method for "table" (AKA "ctrl").
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
}
