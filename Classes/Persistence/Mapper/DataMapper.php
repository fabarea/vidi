<?php
namespace TYPO3\CMS\Vidi\Persistence\Mapper;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * A mapper to map database tables configured in $TCA on domain objects.
 * @todo clean me up! This class has been kept for compatibility reasons with DbBackend.php
 */
class DataMapper implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Returns a data map for a given class name
	 *
	 * @param string $className The class name you want to fetch the Data Map for
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap The data map
	 */
	public function getDataMap($className) {
		return $className;
//		if (!is_string($className) || strlen($className) === 0) {
//			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception('No class name was given to retrieve the Data Map for.', 1251315965);
//		}
//		if (!isset($this->dataMaps[$className])) {
//
//			$this->dataMaps[$className] = $this->dataMapFactory->buildDataMap($className);
//			var_dump($className);
//			exit();
//		}
//		return $this->dataMaps[$className];
	}

	/**
	 * Returns the selector (table) name for a given class name.
	 *
	 * @param string $className
	 * @return string The selector name
	 */
	public function convertClassNameToTableName($className = NULL) {
		return $className;
//		if ($className !== NULL) {
//			$tableName = $this->getDataMap($className)->getTableName();
//		} else {
//			$tableName = strtolower($className);
//		}
//		return $tableName;
	}

	/**
	 * Returns the column name for a given property name of the specified class.
	 *
	 * @param string $propertyName
	 * @param string $className
	 * @return string The column name
	 */
	public function convertPropertyNameToColumnName($propertyName, $className = NULL) {
		return $propertyName;
//		if (!empty($className)) {
//			$dataMap = $this->getDataMap($className);
//			if ($dataMap !== NULL) {
//				$columnMap = $dataMap->getColumnMap($propertyName);
//				if ($columnMap !== NULL) {
//					return $columnMap->getColumnName();
//				}
//			}
//		}
//		return \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
	}

	/**
	 * Returns the type of a child object.
	 *
	 * @param string $parentClassName The class name of the object this proxy is part of
	 * @param string $propertyName The name of the proxied property in it's parent
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException
	 * @return string The class name of the child object
	 */
	public function getType($parentClassName, $propertyName) {
		return $parentClassName;
//		$propertyMetaData = $this->reflectionService->getClassSchema($parentClassName)->getProperty($propertyName);
//		if (!empty($propertyMetaData['elementType'])) {
//			$type = $propertyMetaData['elementType'];
//		} elseif (!empty($propertyMetaData['type'])) {
//			$type = $propertyMetaData['type'];
//		} else {
//			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException('Could not determine the child object type.', 1251315967);
//		}
//		return $type;
	}
}

?>