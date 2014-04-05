<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Uri;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Render a create URI given a data type.
 */
class CreateViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected $backendUser;

	/**
	 * @var \TYPO3\CMS\Vidi\ModuleLoader
	 */
	protected $moduleLoader;

	/**
	 * Initialize View Helper
	 */
	public function initialize() {
		$this->backendUser = $GLOBALS['BE_USER'];
		$this->moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ModuleLoader');
	}

	/**
	 * Render a create URI given a data type.
	 *
	 * @return string
	 */
	public function render() {
		return sprintf('alt_doc.php?returnUrl=%s&edit[%s][%s]=new',
			rawurlencode(BackendUtility::getModuleUrl(GeneralUtility::_GP('M'))),
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
		$isRootLevel = TcaService::table()->get('rootLevel');
		if ($isRootLevel) {
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
}
