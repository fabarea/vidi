<?php
namespace TYPO3\CMS\Vidi\Resolver;

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
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class for retrieving value from a field name and path.
 */
class FieldPathResolver implements SingletonInterface {

	/**
	 * Remove the prefixing path from the file name.
	 *
	 * @param string $fieldNameAndPath
	 * @param string $dataType
	 * @return string
	 */
	public function stripFieldPath($fieldNameAndPath, $dataType = '') {

		$dataType = $this->getContextualDataType($dataType);

		if ($this->containsPath($fieldNameAndPath, $dataType)) {

			// Corresponds to the field name of the foreign table.
			$fieldParts = GeneralUtility::trimExplode('.', $fieldNameAndPath);
			$fieldName = $fieldParts[1];
		} else {
			$fieldName = $fieldNameAndPath;
		}
		return $fieldName;
	}

	/**
	 * Remove the suffixing field name
	 *
	 * @param string $fieldNameAndPath
	 * @param string $dataType
	 * @return string
	 */
	public function stripFieldName($fieldNameAndPath, $dataType = '') {

		$dataType = $this->getContextualDataType($dataType);

		if ($this->containsPath($fieldNameAndPath, $dataType)) {

			// Corresponds to the field name of the foreign table.
			$fieldParts = GeneralUtility::trimExplode('.', $fieldNameAndPath);
			$fieldName = $fieldParts[0];
		} else {
			$fieldName = $fieldNameAndPath;
		}
		return $fieldName;
	}

	/**
	 * Returns the class names to be applied to a cell ("td").
	 *
	 * @param string $fieldNameAndPath
	 * @param string $dataType
	 * @return string
	 */
	public function getDataType($fieldNameAndPath, $dataType = '') {

		$dataType = $this->getContextualDataType($dataType);

		if ($this->containsPath($fieldNameAndPath, $dataType)) {

			// Compute the foreign data type.
			$fieldParts = GeneralUtility::trimExplode('.', $fieldNameAndPath);
			$fieldNameAndPath = $fieldParts[0];
			$dataType = TcaService::table($dataType)->field($fieldNameAndPath)->getForeignTable();
		}
		return $dataType;
	}

	/**
	 * Return the data type according to the context.
	 *
	 * @param $dataType
	 * @return string
	 */
	public function getContextualDataType($dataType) {

		if (!$dataType) {
			$dataType = $this->getModuleLoader()->getDataType();
		}
		return $dataType;
	}

	/**
	 * Tell whether the field name contains a path, e.g. metadata.title
	 * But resolves the case when the field is composite e.g "items.sys_file_metadata" and looks as field path but is not!
	 * A composite field = a field for a MM relation  of type "group" where the table name is appended.
	 *
	 * @param string $fieldNameAndPath
	 * @param string $dataType
	 * @return boolean
	 */
	public function containsPath($fieldNameAndPath, $dataType) {
		$doesContainPath = strpos($fieldNameAndPath, '.') > 0 && TcaService::table($dataType)->hasNotField($fieldNameAndPath); // -> will make sure it is not a composite field name.
		return $doesContainPath;
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}
}
