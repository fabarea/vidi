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
		$menu = ''; // Initialize variable as string.
		if ($this->getModuleLoader()->isCurrentModuleList()) {
			$moduleCodes = ModuleService::getInstance()->getModulesForCurrentPid();
			$pid = $this->getModuleLoader()->getCurrentPid();
			$menu .= $this->assembleDataTypeTab($pid, $moduleCodes);
		}
		return $menu;
	}


	/**
	 * @param int $pid
	 * @param array $moduleCodes
	 * @return string
	 */
	protected function assembleDataTypeTab($pid, array $moduleCodes) {

		return sprintf('<form id="form-dataType" action="%s">
		<input type="hidden" name="M" value="%s"/>
		<input type="hidden" name="moduleToken" value="%s"/>
		<input type="hidden" name="id" value="%s"/>
		<select id="menu-dataType" class="btn btn-mini" name="%s" onchange="$(this).parent().submit()">%s</select>
		</form>',
			'mod.php', // We can not use BackendUtility::getModuleUrl() here as the GET parameters are reset in a GET method.
			GeneralUtility::_GET('M'),
			$this->getModuleToken(),
			GeneralUtility::_GET('id'),
			Parameter::SUBMODULE,
			$this->assembleMenuOptions($moduleCodes)
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

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}
}
