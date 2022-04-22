<?php
namespace Fab\Vidi\ViewHelpers;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which connects with the BE user data.
 */
class UserPreferencesViewHelper extends AbstractViewHelper
{

    /**
     * @var AbstractFrontend
     */
    protected $cacheInstance;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('key', 'string', '', true);
    }

    /**
     * Interface with the BE user data.
     *
     * @return string
     */
    public function render()
    {
        $this->initializeCache();
        $key = $this->getModuleLoader()->getDataType() . '_' . $this->getBackendUserIdentifier() . '_' . $this->arguments['key'];

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
     * @return ModuleLoader|object
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }

    /**
     * Initialize cache instance to be ready to use
     *
     * @return void
     */
    protected function initializeCache()
    {
        $this->cacheInstance = $this->getCacheManager()->getCache('vidi');
    }

    /**
     * Return the Cache Manager
     *
     * @return CacheManager|object
     */
    protected function getCacheManager()
    {
        return GeneralUtility::makeInstance(CacheManager::class);
    }

}
