<?php
namespace TYPO3\CMS\Vidi\Resolver;
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
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class for retrieving value from a field name and path.
 */
class FieldPathResolver implements SingletonInterface {

	/**
	 * Remove the prefixing path from the file name.
	 *
	 * @param string $fieldNameAndPath
	 * @return string
	 * @deprecated used stripFieldPath, will be removed in Vidi 0.3.0 + 2.
	 */
	public function stripPath($fieldNameAndPath) {
		return $this->stripFieldPath($fieldNameAndPath);
	}

	/**
	 * Remove the prefixing path from the file name.
	 *
	 * @param string $fieldNameAndPath
	 * @return string
	 */
	public function stripFieldPath($fieldNameAndPath) {
		$fieldName = $fieldNameAndPath;
		if ($this->containsPath($fieldName)) {
			// Corresponds to the field name of the foreign table.
			$fieldParts = GeneralUtility::trimExplode('.', $fieldNameAndPath);
			$fieldName = $fieldParts[1];
		}
		return $fieldName;
	}

	/**
	 * Remove the suffixing field name
	 *
	 * @param string $fieldNameAndPath
	 * @return string
	 */
	public function stripFieldName($fieldNameAndPath) {
		$fieldName = $fieldNameAndPath;
		if ($this->containsPath($fieldName)) {

			// Corresponds to the field name of the foreign table.
			$fieldParts = GeneralUtility::trimExplode('.', $fieldNameAndPath);
			$fieldName = $fieldParts[0];
		}
		return $fieldName;
	}

	/**
	 * Returns the class names to be applied to a cell ("td").
	 *
	 * @param string $fieldNameAndPath
	 * @return string
	 */
	public function getDataType($fieldNameAndPath) {
		$resolvedDataType = $this->getModuleLoader()->getDataType();
		if ($this->containsPath($fieldNameAndPath)) {

			// Compute the foreign data type.
			$fieldParts = GeneralUtility::trimExplode('.', $fieldNameAndPath);
			$fieldNameAndPath = $fieldParts[0];
			$resolvedDataType = TcaService::table($resolvedDataType)->field($fieldNameAndPath)->getForeignTable();
		}
		return $resolvedDataType;
	}

	/**
	 * Tell whether the field name contains a path, e.g. metadata.title
	 *
	 * @param string $fieldName
	 * @return boolean
	 */
	public function containsPath($fieldName) {
		return strpos($fieldName, '.') > 0;
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
