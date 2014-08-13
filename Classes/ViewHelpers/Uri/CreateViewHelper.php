<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Uri;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Module\Parameter;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Render a create URI given a data type.
 */
class CreateViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected $backendUser;

	/**
	 * @var \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected $moduleLoader;

	/**
	 * Initialize View Helper
	 */
	public function initialize() {
		$this->backendUser = $GLOBALS['BE_USER'];
		$this->moduleLoader = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}

	/**
	 * Render a create URI given a data type.
	 *
	 * @return string
	 */
	public function render() {
		return sprintf('alt_doc.php?returnUrl=%s&edit[%s][%s]=new',
			rawurlencode($this->getModuleLoader()->getModuleUrl()),
			rawurlencode($this->moduleLoader->getDataType()),
			$this->getPid()
		);
	}

	/**
	 * Return the default configured pid.
	 *
	 * @return int
	 */
	public function getPid() {
		if (GeneralUtility::_GP(Parameter::PID)) {
			$pid = GeneralUtility::_GP(Parameter::PID);
		} elseif (TcaService::table()->get('rootLevel')) {
			$pid = 0;
		} else {
			// Get configuration from User TSconfig if any
			$tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->moduleLoader->getDataType());
			$result = $this->backendUser->getTSConfig($tsConfigPath);
			$pid = $result['value'];

			// Get pid from Module Loader
			if (NULL === $pid) {
				$pid = $this->moduleLoader->getDefaultPid();
			}
		}
		return $pid;
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
