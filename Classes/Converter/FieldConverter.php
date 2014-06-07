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
 * Convert a field name to property name.
 */
class FieldConverter {

	/**
	 * @param string|Content $tableNameOrContentObject
	 * @param string $propertyName
	 * @return string
	 */
	static public function toField($tableNameOrContentObject, $propertyName) {

		// Resolve the table name.
		$tableName = $tableNameOrContentObject instanceof Content ? $tableNameOrContentObject->getDataType() : $tableNameOrContentObject;

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

		return $fieldName;
	}

}
