<?php
namespace TYPO3\CMS\Vidi\Override\Backend\View;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@ecodev.ch>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * class to render the TYPO3 backend menu for the modules
 *
 * @todo remove me when Media will have TYPO3 6.2 as prerequisite. This code is now part of the Core (6.2).
 *
 * @author Fabien Udriot <fabien.udriot@ecodev.ch>
 */
class ModuleMenuView extends \TYPO3\CMS\Backend\View\ModuleMenuView {

	/**
	 * Reads User configuration from options.hideModules and removes
	 * modules from $this->loadedModules accordingly.
	 *
	 * @return void
	 */
	protected function unsetHiddenModules() {

		// Hide modules if set in userTS.
		$hiddenModules = $GLOBALS['BE_USER']->getTSConfig('options.hideModules');

		if (!empty($hiddenModules['value'])) {
			$hiddenMainModules = GeneralUtility::trimExplode(',', $hiddenModules['value'], TRUE);
			foreach ($hiddenMainModules as $hiddenMainModule) {
				unset($this->loadedModules[$hiddenMainModule]);
			}
		}

		// Hide sub-modules if set in userTS.
		if (!empty($hiddenModules['properties']) && is_array($hiddenModules['properties'])) {
			foreach ($hiddenModules['properties'] as $mainModuleName => $subModules) {
				$hiddenSubModules = GeneralUtility::trimExplode(',', $subModules, TRUE);
				foreach ($hiddenSubModules as $hiddenSubModule) {
					unset($this->loadedModules[$mainModuleName]['sub'][$hiddenSubModule]);
				}
			}
		}
	}

	/**
	 * gets the raw module data
	 *
	 * @return array Multi dimension array with module data
	 */
	public function getRawModuleData() {
		$modules = array();
		// Remove the 'doc' module?
		if ($GLOBALS['BE_USER']->getTSConfigVal('options.disableDocModuleInAB')) {
			unset($this->loadedModules['doc']);
		}

		$this->unsetHiddenModules();

		foreach ($this->loadedModules as $moduleName => $moduleData) {
			$moduleLink = '';
			if (!is_array($moduleData['sub'])) {
				$moduleLink = $moduleData['script'];
			}
			$moduleLink = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveBackPath($moduleLink);
			$moduleKey = 'modmenu_' . $moduleName;
			$moduleIcon = $this->getModuleIcon($moduleKey);
			$modules[$moduleKey] = array(
				'name' => $moduleName,
				'title' => $GLOBALS['LANG']->moduleLabels['tabs'][$moduleName . '_tab'],
				'onclick' => 'top.goToModule(\'' . $moduleName . '\');',
				'icon' => $moduleIcon,
				'link' => $moduleLink,
				'description' => $GLOBALS['LANG']->moduleLabels['labels'][$moduleKey . 'label']
			);
			if (!is_array($moduleData['sub']) && $moduleData['script'] != 'dummy.php') {
				// Work around for modules with own main entry, but being self the only submodule
				$modules[$moduleKey]['subitems'][$moduleKey] = array(
					'name' => $moduleName,
					'title' => $GLOBALS['LANG']->moduleLabels['tabs'][$moduleName . '_tab'],
					'onclick' => 'top.goToModule(\'' . $moduleName . '\');',
					'icon' => $this->getModuleIcon($moduleName . '_tab'),
					'link' => $moduleLink,
					'originalLink' => $moduleLink,
					'description' => $GLOBALS['LANG']->moduleLabels['labels'][$moduleKey . 'label'],
					'navigationFrameScript' => NULL,
					'navigationFrameScriptParam' => NULL,
					'navigationComponentId' => NULL
				);
			} elseif (is_array($moduleData['sub'])) {
				foreach ($moduleData['sub'] as $submoduleName => $submoduleData) {
					$submoduleLink = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveBackPath($submoduleData['script']);
					$submoduleKey = $moduleName . '_' . $submoduleName . '_tab';
					$submoduleIcon = $this->getModuleIcon($submoduleKey);
					$submoduleDescription = $GLOBALS['LANG']->moduleLabels['labels'][$submoduleKey . 'label'];
					$originalLink = $submoduleLink;
					$modules[$moduleKey]['subitems'][$submoduleKey] = array(
						'name' => $moduleName . '_' . $submoduleName,
						'title' => $GLOBALS['LANG']->moduleLabels['tabs'][$submoduleKey],
						'onclick' => 'top.goToModule(\'' . $moduleName . '_' . $submoduleName . '\');',
						'icon' => $submoduleIcon,
						'link' => $submoduleLink,
						'originalLink' => $originalLink,
						'description' => $submoduleDescription,
						'navigationFrameScript' => $submoduleData['navFrameScript'],
						'navigationFrameScriptParam' => $submoduleData['navFrameScriptParam'],
						'navigationComponentId' => $submoduleData['navigationComponentId']
					);
					if ($moduleData['navFrameScript']) {
						$modules[$moduleKey]['subitems'][$submoduleKey]['parentNavigationFrameScript'] = $moduleData['navFrameScript'];
					}
				}
			}
		}
		return $modules;
	}
}
