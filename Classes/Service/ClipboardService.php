<?php
namespace Fab\Vidi\Service;

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

use Fab\Vidi\Persistence\Matcher;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to interact with the Vidi clipboard.
 */
class ClipboardService implements SingletonInterface {

	/**
	 * Get the Matcher object of the clipboard.
	 *
	 * @return Matcher
	 */
	public function getMatcher() {
		$matcher = $this->getBackendUser()->getModuleData($this->getDataKey());
		if (!$matcher) {
			/** @var $matcher Matcher */
			$matcher = GeneralUtility::makeInstance('Fab\Vidi\Persistence\Matcher');
		}
		return $matcher;
	}

	/**
	 * Tell whether the clipboard has items or not.
	 *
	 * @return bool
	 */
	public function hasItems() {
		$matcher = $this->getMatcher();

		$inCriteria = $matcher->getInCriteria();
		$likeCriteria = $matcher->getLikeCriteria();
		$searchTerm = $matcher->getSearchTerm();

		$hasItems = !empty($inCriteria) || !empty($likeCriteria) || !empty($searchTerm);
		return $hasItems;
	}

	/**
	 * Save data into the clipboard.
	 *
	 * @param Matcher $matches
	 */
	public function save(Matcher $matches) {
		$this->getBackendUser()->pushModuleData($this->getDataKey(), $matches);
	}

	/**
	 * Completely empty the clipboard for a data type.
	 *
	 * @return void
	 */
	public function flush() {
		$this->getBackendUser()->pushModuleData($this->getDataKey(), NULL);
	}

	/**
	 * @return string
	 */
	protected function getDataKey() {
		return 'vidi_clipboard_' . $this->getModuleLoader()->getDataType();
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \Fab\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
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
