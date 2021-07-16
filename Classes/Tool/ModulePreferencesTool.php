<?php
namespace Fab\Vidi\Tool;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Module\ModulePreferences;
use Fab\Vidi\Module\ConfigurablePart;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Core\Environment;
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
        $view->assign('sitePath', Environment::getPublicPath() . '/');
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
        $excludedFields = [];
        if (!empty($tca['excluded_fields'])) {
            $excludedFields = GeneralUtility::trimExplode(',', $tca['excluded_fields'], true);
        } elseif (!empty($tca['export']['excluded_fields'])) { // only for export for legacy reason.
            $excludedFields = GeneralUtility::trimExplode(',', $tca['export']['excluded_fields'], true);
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
     * @return \Fab\Vidi\Module\ModuleLoader|object
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleLoader::class);
    }

    /**
     * @return ModulePreferences|object
     */
    protected function getModulePreferences()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Module\ModulePreferences::class);
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Grid\GridAnalyserService|object
     */
    protected function getGridAnalyserService()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Grid\GridAnalyserService::class);
    }
}

