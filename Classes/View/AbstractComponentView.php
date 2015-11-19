<?php
namespace Fab\Vidi\View;

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
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance('TYPO3\CMS\Lang\LanguageService');
    }

    /**
     * @return IconFactory
     */
    protected function getIconFactory()
    {
        return GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * @return LinkButton
     */
    protected function makeLinkButton()
    {
        return GeneralUtility::makeInstance(LinkButton::class);
    }

}
