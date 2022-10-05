<?php

namespace Fab\Vidi\View;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract Component View.
 */
abstract class AbstractComponentView implements ViewComponentInterface
{
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
     * Returns an instance of the current Backend User.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService|object
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class);
    }

    /**
     * @return IconFactory|object
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * @return LinkButton|object
     */
    protected function makeLinkButton()
    {
        return GeneralUtility::makeInstance(LinkButton::class);
    }
}
