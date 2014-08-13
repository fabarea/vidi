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
