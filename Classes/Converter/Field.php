<?php
namespace TYPO3\CMS\Vidi\Converter;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Fabien Udriot <fabien.udriot@typo3.org>
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

/**
 * Convert a field name to property name.
 */
class Field implements SingletonInterface {

	/**
	 * @var string
	 */
	static protected $currentField;

	/**
	 * @var string
	 */
	static protected $currentTable;

	/**
	 * @var array
	 */
	protected $storage = array();

	/**
	 * @param string $fieldName
	 * @return \TYPO3\CMS\Vidi\Converter\Field
	 */
	static public function name($fieldName) {
		self::$currentField = $fieldName;
		self::$currentTable = ''; // reset the table name value.
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Converter\Field');
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
	public function toPropertyName() {

		$fieldName = $this->getFieldName();
		$tableName = $this->getTableName();

		if (empty($this->storage[$tableName][$fieldName])) {
			if ($this->storage[$tableName]) {
				$this->storage[$tableName] = array();
			}

			// Special case when the field name does not follow the conventions "field_name" => "fieldName".
			// Rely on mapping for those cases.
			if (!empty($GLOBALS['TCA'][$tableName]['vidi']['mappings'][$fieldName])) {
				$propertyName = $GLOBALS['TCA'][$tableName]['vidi']['mappings'][$fieldName];
			} else {
				$propertyName = GeneralUtility::underscoredToLowerCamelCase($fieldName);
			}

			$this->storage[$tableName][$fieldName] = $propertyName;
		}

		return $this->storage[$tableName][$fieldName];
	}

	/**
	 * @return string
	 * @deprecated use toPropertyName. Will be removed in 0.3.0 + 2 version.
	 */
	public function toProperty() {
		return $this->toPropertyName();
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	protected function getFieldName() {
		$fieldName = self::$currentField;
		if (empty($fieldName)) {
			throw new \Exception('I could not find a field name value.', 1403203290);
		}
		return $fieldName;
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
