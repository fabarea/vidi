<?php
namespace TYPO3\CMS\Vidi\View\Tab;

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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Module\Parameter;
use TYPO3\CMS\Vidi\Module\ModuleService;
use TYPO3\CMS\Vidi\View\AbstractComponentView;

/**
 * View component which renders a data type menu for the List2 module.
 */
class DataTypeTab extends AbstractComponentView {

	/**
	 * Renders a "new" button to be placed in the doc header.
	 *
	 * @return string
	 */
	public function render() {
		$output = ''; // Initialize variable as string.
		if ($this->getModuleLoader()->copeWithPageTree()) {
			$moduleCodes = ModuleService::getInstance()->getModulesForCurrentPid();
			$output = $this->assembleDataTypeTab($moduleCodes);
		}
		return $output;
	}

	/**
	 * @param array $moduleCodes
	 * @return string
	 */
	protected function assembleDataTypeTab(array $moduleCodes) {
		return sprintf('<ul class="nav nav-tabs">%s</ul>',
			$this->assembleTab($moduleCodes)
		);
	}

	/**
	 * @return string
	 */
	protected function getModuleToken() {
		$moduleName = GeneralUtility::_GET(Parameter::MODULE);
		return FormProtectionFactory::get()->generateToken('moduleCall', $moduleName);
	}

	/**
	 * @param array $moduleCodes
	 * @return string
	 */
	protected function assembleTab(array $moduleCodes) {
		$tabs = array();
		foreach ($moduleCodes as $moduleCode => $title) {
			$dataType = $this->getDataTypeForModuleCode($moduleCode);
			$tabs[] = sprintf('<li %s><a href="%s">%s %s</a></li>',
				$this->getModuleLoader()->getVidiModuleCode() === $moduleCode ? 'class="active"' : '',
				$this->getModuleLoader()->getModuleUrl(array(Parameter::SUBMODULE => $moduleCode)),
				IconUtility::getSpriteIconForRecord($dataType, array()),
				$title
			);
		}
		return implode("\n", $tabs);
	}

	/**
	 * @param $moduleCode
	 * @return string
	 */
	protected function getDataTypeForModuleCode($moduleCode) {
		return $GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode]['dataType'];
	}

	/**
	 * @param array $moduleCodes
	 * @return string
	 */
	protected function assembleMenuOptions(array $moduleCodes) {
		$options = '';
		foreach ($moduleCodes as $moduleCode => $title) {
			$options .= sprintf('<option class="menu-dataType-item" value="%s" style="background-url(sysext/t3skin/icons/gfx/i/pages.gif)" %s>%s</option>%s',
				$moduleCode,
				$this->getModuleLoader()->getVidiModuleCode() === $moduleCode ? 'selected' : '',
				$title,
				chr(10)
			);
		}

		return $options;
	}

}
