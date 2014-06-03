<?php
namespace TYPO3\CMS\Vidi\Configuration;
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

		/** @var \TYPO3\CMS\Vidi\ModuleLoader $moduleLoader */
		$moduleLoader = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ModuleLoader');

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