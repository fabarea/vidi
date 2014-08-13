<?php
namespace TYPO3\CMS\Vidi\Converter;

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
use TYPO3\CMS\Vidi\Domain\Model\Content;

/**
 * Convert a property name to field.
 */
class Property {

	/**
	 * @var string
	 */
	static protected $currentProperty;

	/**
	 * @var string
	 */
	static protected $currentTable;

	/**
	 * @var array
	 */
	protected $storage = array();

	/**
	 * @param string $propertyName
	 * @return \TYPO3\CMS\Vidi\Converter\Property
	 */
	static public function name($propertyName) {
		self::$currentProperty = $propertyName;
		self::$currentTable = ''; // reset the table name value.
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Converter\Property');
	}

	/**
	 * @param string|Content $tableNameOrContentObject
	 * @return $this
	 */
	public function of($tableNameOrContentObject) {
	    // Resolve the table name.
		self::$currentTable = $tableNameOrContentObject instanceof Content ?
			$tableNameOrContentObject->getDataType() :
			$tableNameOrContentObject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function toFieldName() {

		$propertyName = $this->getPropertyName();
		$tableName = $this->getTableName();

		if (empty($this->storage[$tableName][$propertyName])) {
			if ($this->storage[$tableName]) {
				$this->storage[$tableName] = array();
			}

			// Default case
			$fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);

			// Special case in case the field name does not follow the conventions "field_name" => "fieldName"
			// There is the chance to make some mapping
			if (!empty($GLOBALS['TCA'][$tableName]['vidi']['mappings'])) {
				$key = array_search($propertyName, $GLOBALS['TCA'][$tableName]['vidi']['mappings']);
				if ($key !== FALSE) {
					$fieldName = $key;
				}
			}

			$this->storage[$tableName][$propertyName] = $fieldName;
		}

		return $this->storage[$tableName][$propertyName];
	}

	/**
	 * @return string
	 * @deprecated use toFieldName. Will be removed in 0.3.0 + 2 version.
	 */
	public function toField() {
		return $this->toFieldName();
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function getPropertyName() {
		$propertyName = self::$currentProperty;
		if (empty($propertyName)) {
			throw new \Exception('I could not find a field name value.', 1403203290);
		}
		return $propertyName;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function getTableName() {
		$tableName = self::$currentTable;
		if (empty($tableName)) {
			throw new \Exception('I could not find a table name value.', 1403203291);
		}
		return $tableName;
	}

}
