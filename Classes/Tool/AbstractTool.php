<?php
namespace TYPO3\CMS\Vidi\Tool;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract Tool
 */
abstract class AbstractTool implements ToolInterface {

	/**
	 * @param string $templateNameAndPath
	 * @return \TYPO3\CMS\Fluid\View\StandaloneView
	 */
	protected function initializeStandaloneView($templateNameAndPath) {

		$templateNameAndPath = GeneralUtility::getFileAbsFileName($templateNameAndPath);

		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
		$view = $this->getObjectManager()->get('TYPO3\CMS\Fluid\View\StandaloneView');

		$view->setTemplatePathAndFilename($templateNameAndPath);
		return $view;
	}

	/**
	 * Returns an instance of the current Backend User.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
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
