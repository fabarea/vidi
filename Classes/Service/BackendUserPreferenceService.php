<?php
namespace TYPO3\CMS\Vidi\Service;

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
