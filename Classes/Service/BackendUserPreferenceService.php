<?php
namespace TYPO3\CMS\Vidi\Service;
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
 * A class dealing with BE User preference.
 */
class BackendUserPreferenceService {

	/**
	 * Returns a class instance
	 *
	 * @return \TYPO3\CMS\Vidi\Service\BackendUserPreferenceService
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('\TYPO3\CMS\Vidi\Service\BackendUserPreferenceService');
	}

	/**
	 * Returns a configuration key for the current BE User.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		$result = '';
		if ($this->getBackendUser() && !empty($this->getBackendUser()->uc[$key])) {
			$result = $this->getBackendUser()->uc[$key];

		}
		return $result;
	}

	/**
	 * Set a configuration for the current BE User.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set($key, $value) {
		if ($this->getBackendUser()) {
			$this->getBackendUser()->uc[$key] = $value;
			$this->getBackendUser()->writeUC();
		}
	}

	/**
	 * Returns an instance of the current Backend User.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}
}
