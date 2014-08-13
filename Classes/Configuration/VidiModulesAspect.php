<?php
namespace TYPO3\CMS\Vidi\Configuration;

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

use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Initialize Vidi modules
 */
class VidiModulesAspect implements TableConfigurationPostProcessingHookInterface {

	/**
	 * Initialize and populate TBE_MODULES_EXT with default data.
	 *
	 * @return void
	 */
	public function processData() {

		/** @var \TYPO3\CMS\Vidi\Module\ModuleLoader $moduleLoader */
		$moduleLoader = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');

		// Each data data can be displayed in a Vidi module
		foreach ($GLOBALS['TCA'] as $dataType => $configuration) {
			if (!$moduleLoader->isRegistered($dataType)) {

				// @todo some modules have TSConfig configuration for not being displayed. Should be respected!
				$moduleLoader->setDataType($dataType)
					->isShown(FALSE)
					->register();
			}
		}
	}

}