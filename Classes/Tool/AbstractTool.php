<?php
namespace Fab\Vidi\Tool;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract Tool
 */
abstract class AbstractTool implements ToolInterface
{

    /**
     * @param string $templateNameAndPath
     * @return \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected function initializeStandaloneView($templateNameAndPath)
    {

        $templateNameAndPath = GeneralUtility::getFileAbsFileName($templateNameAndPath);

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->getObjectManager()->get('TYPO3\CMS\Fluid\View\StandaloneView');

        $view->setTemplatePathAndFilename($templateNameAndPath);
        return $view;
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
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Module\ModuleLoader
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
    }

}
