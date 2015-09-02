<?php
namespace Fab\Vidi\Controller\Backend;

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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller which handles actions related to Vidi in the Backend.
 */
class UserPreferencesController extends ActionController {

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend
	 */
	protected $cacheInstance;

	/**
	 * @param string $key
	 * @param string $value
	 * @param string $preferenceSignature
	 * @return string
	 */
	public function saveAction($key, $value, $preferenceSignature) {

		$this->initializeCache();

		$dataType = $this->getModuleLoader()->getDataType();

		$key = $dataType . '_' . $this->getBackendUserIdentifier() . '_' . $key;
		$this->cacheInstance->set($key, $value, array(), 0);

		$key = $dataType . '_' . $this->getBackendUserIdentifier() . '_signature';
		$this->cacheInstance->set($key, $preferenceSignature, array(), 0);

		return 'OK';
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
