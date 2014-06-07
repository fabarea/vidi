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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Domain\Model\Content;

/**
 * Convert a property name to field.
 */
class PropertyConverter {

	/**
	 * @param string|Content $tableNameOrContentObject
	 * @param string $fieldName
	 * @return string
	 */
	static public function toProperty($tableNameOrContentObject, $fieldName) {

		// Resolve the table name.
		$tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;

		// Special case when the field name does not follow the conventions "field_name" => "fieldName".
		// Rely on mapping for those cases.
		if (!empty($GLOBALS['TCA'][$tableName]['vidi']['mappings'][$fieldName])) {
			$propertyName = $GLOBALS['TCA'][$tableName]['vidi']['mappings'][$fieldName];
		} else {
			$propertyName = GeneralUtility::underscoredToLowerCamelCase($fieldName);
		}
		return $propertyName;
	}

}
