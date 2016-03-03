<?php
namespace Fab\Vidi\Tool;

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

use Fab\Vidi\Module\ModulePreferences;
use Fab\Vidi\Module\ConfigurablePart;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Module preferences for a Vidi module.
 */
class ModulePreferencesTool extends AbstractTool
{

    /**
     * Display the title of the tool on the welcome screen.
     *
     * @return string
     */
    public function getTitle()
    {
        return LocalizationUtility::translate(
            'module_preferences_for',
            'vidi',
            array(Tca::table($this->getModuleLoader()->getDataType())->getTitle())
        );
    }

    /**
     * Display the description of the tool in the welcome screen.
     *
     * @return string
     */
    public function getDescription()
    {
        $templateNameAndPath = 'EXT:vidi/Resources/Private/Standalone/Tool/ModulePreferences/Launcher.html';
        $view = $this->initializeStandaloneView($templateNameAndPath);
        $view->assign('sitePath', PATH_site);
        return $view->render();
    }

    /**
     * Do the job!
     *
     * @param array $arguments
     * @return string
     */
    public function work(array $arguments = array())
    {


        if (isset($arguments['save'])) {

            // Revert visible <-> excluded
            $excludedFields = array_diff(
                Tca::grid()->getAllFieldNames(),
                $arguments['excluded_fields'],
                $this->getExcludedFieldsFromTca()
            );
            $arguments['excluded_fields'] = $excludedFields;
            $this->getModulePreferences()->save($arguments);
        }

        $templateNameAndPath = 'EXT:vidi/Resources/Private/Standalone/Tool/ModulePreferences/WorkResult.html';
        $view = $this->initializeStandaloneView($templateNameAndPath);

        $view->assign('title', Tca::table($this->getModuleLoader()->getDataType())->getTitle());

        // Fetch the menu of visible items.
        $menuVisibleItems = $this->getModulePreferences()->get(ConfigurablePart::MENU_VISIBLE_ITEMS);
        $view->assign(ConfigurablePart::MENU_VISIBLE_ITEMS, $menuVisibleItems);

        // Fetch the default number of menu visible items.
        $menuDefaultVisible = $this->getModulePreferences()->get(ConfigurablePart::MENU_VISIBLE_ITEMS_DEFAULT);
        $view->assign(ConfigurablePart::MENU_VISIBLE_ITEMS_DEFAULT, $menuDefaultVisible);

        // Get the visible columns
        $view->assign('columns', Tca::grid()->getAllFieldNames());

        return $view->render();
    }

    /**
     * @return array
     */
    protected function getExcludedFieldsFromTca()
    {
        $tca = Tca::grid()->getTca();
        $excludedFields = array();
        if (!empty($tca['excluded_fields'])) {
            $excludedFields = GeneralUtility::trimExplode(',', $tca['excluded_fields'], TRUE);
        } elseif (!empty($tca['export']['excluded_fields'])) { // only for export for legacy reason.
            $excludedFields = GeneralUtility::trimExplode(',', $tca['export']['excluded_fields'], TRUE);
        }
        return $excludedFields;
    }

    /**
     * Tell whether the tools should be displayed according to the context.
     *
     * @return bool
     */
    public function isShown()
    {
        return $this->getBackendUser()->isAdmin();
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

    /**
     * @return ModulePreferences
     */
    protected function getModulePreferences()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Module\ModulePreferences');
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Grid\GridAnalyserService
     */
    protected function getGridAnalyserService()
    {
        return GeneralUtility::makeInstance('Fab\Vidi\Grid\GridAnalyserService');
    }
}

