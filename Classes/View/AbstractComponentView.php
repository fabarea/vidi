<?php
namespace Fab\Vidi\View;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
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
     * @return \Fab\Vidi\Module\ModuleLoader
     * @throws \InvalidArgumentException
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     * @throws \InvalidArgumentException
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance('TYPO3\CMS\Lang\LanguageService');
    }

    /**
     * @return IconFactory
     * @throws \InvalidArgumentException
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * @return LinkButton
     * @throws \InvalidArgumentException
     */
    protected function makeLinkButton()
    {
        return GeneralUtility::makeInstance(LinkButton::class);
    }

}
