<?php

namespace Fab\Vidi\Controller;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend;
use TYPO3\CMS\Core\Cache\CacheManager;
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Controller which handles actions related to Vidi in the Backend.
 */
class UserPreferencesController extends ActionController
{
    /**
     * @param string $key
     * @param string $value
     * @param string $preferenceSignature
     * @return string
     */
    public function saveAction($key, $value, $preferenceSignature): ResponseInterface
    {
        $dataType = $this->getModuleLoader()->getDataType();

        $key = $dataType . '_' . $this->getBackendUserIdentifier() . '_' . $key;
        $this->getCacheInstance()->set($key, $value, [], 0);

        $key = $dataType . '_' . $this->getBackendUserIdentifier() . '_signature';
        $this->getCacheInstance()->set($key, $preferenceSignature, [], 0);

        return $this->htmlResponse('OK');
    }

    /**
     * @return int
     */
    protected function getBackendUserIdentifier()
    {
        return $this->getBackendUser()->user['uid'];
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return ModuleLoader
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }

    /**
     * @return AbstractFrontend
     */
    protected function getCacheInstance()
    {
        return $this->getCacheManager()->getCache('vidi');
    }

    /**
     * Return the Cache Manager
     *
     * @return CacheManager
     */
    protected function getCacheManager()
    {
        return GeneralUtility::makeInstance(CacheManager::class);
    }
}
