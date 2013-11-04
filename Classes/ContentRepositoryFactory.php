<?php
namespace TYPO3\CMS\Vidi;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Factory class to server instances of Content repositories.
 */
class ContentRepositoryFactory implements \TYPO3\CMS\Core\SingletonInterface{

	/**
	 * @var array
	 */
	static protected $instances = array();

	/**
	 * Returns a class instance of a repository.
	 * If not data type is given, get the value from the module loader.
	 *
	 * @throws \RuntimeException
	 * @param string $dataType
	 * @return \TYPO3\CMS\Extbase\Persistence\RepositoryInterface
	 */
	static public function getInstance($dataType = '') {

		/** @var \TYPO3\CMS\Vidi\ModuleLoader $moduleLoader */
		if ($dataType == '') {

			// Try to get the data type from the module loader
			$moduleLoader = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ModuleLoader');
			$dataType = $moduleLoader->getDataType();
		}

		// This should not happen
		if (!$dataType) {
			throw new \RuntimeException('No data type given nor could be fetched by the module loader.', 1376118278);
		}

		if (empty(self::$instances[$dataType])) {
			$className = 'TYPO3\CMS\Vidi\Domain\Repository\ContentRepository';

			/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
			$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
			self::$instances[$dataType] = $objectManager->get($className, $dataType);
		}
		return self::$instances[$dataType];
	}
}
