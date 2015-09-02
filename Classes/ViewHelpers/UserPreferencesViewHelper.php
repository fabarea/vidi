<?php
namespace Fab\Vidi\ViewHelpers;

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

use TYPO3\CMS\Core\Cache\Cache;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which connects with the BE user data.
 */
class UserPreferencesViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
	 */
	protected $cacheInstance;

	/**
	 * Interface with the BE user data.
	 *
	 * @param string $key
	 * @return string
	 */
	public function render($key) {
		$this->initializeCache();
		$key = $this->getModuleLoader()->getDataType() . '_' . $this->getBackendUserIdentifier(). '_' . $key;

		$value = $this->cacheInstance->get($key);
		if ($value) {
			$value = addslashes($value);
		} else {
			$value = '';
		}
		return $value;
	}

	/**
	 * @return int
	 */
	protected function getBackendUserIdentifier() {
		return $this->getBackendUser()->user['uid'];
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
	 * Get the Vidi Module Loader.
	 *
	 * @return \Fab\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
	}

	/**
	 * Initialize cache instance to be ready to use
	 *
	 * @return void
	 */
	protected function initializeCache() {
		Cache::initializeCachingFramework();
		$this->cacheInstance = $this->getCacheManager()->getCache('vidi');
	}

	/**
	 * Return the Cache Manager
	 *
	 * @return \TYPO3\CMS\Core\Cache\CacheManager
	 */
	protected function getCacheManager() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Core\Cache\CacheManager');
	}

}
