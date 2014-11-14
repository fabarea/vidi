<?php
namespace TYPO3\CMS\Vidi\Domain\Repository;

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

/**
 * Factory class to server instances of Content repositories.
 */
class ContentRepositoryFactory implements SingletonInterface {

	/**
	 * @var array
	 */
	static protected $instances = array();

	/**
	 * Returns a class instance of a repository.
	 * If not data type is given, get the value from the module loader.
	 *
	 * @param string $dataType
	 * @param string $sourceFieldName
	 * @return \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository
	 */
	static public function getInstance($dataType = NULL, $sourceFieldName = '') {

		/** @var \TYPO3\CMS\Vidi\Module\ModuleLoader $moduleLoader */
		if (is_null($dataType)) {

			// Try to get the data type from the module loader.
			$moduleLoader = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
			$dataType = $moduleLoader->getDataType();
		}

		// This should not happen
		if (!$dataType) {
			throw new \RuntimeException('No data type given nor could be fetched by the module loader.', 1376118278);
		}

		if (empty(self::$instances[$dataType])) {
			$className = 'TYPO3\CMS\Vidi\Domain\Repository\ContentRepository';
			self::$instances[$dataType] = GeneralUtility::makeInstance($className, $dataType, $sourceFieldName);
		}

		/** @var ContentRepository $contentRepository */
		$contentRepository = self::$instances[$dataType];
		$contentRepository->setSourceFieldName($sourceFieldName);
		return $contentRepository;
	}

}
