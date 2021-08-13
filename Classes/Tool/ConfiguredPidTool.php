<?php
namespace Fab\Vidi\Tool;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Module\ModulePidService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class ConfiguredPidTool
 */
class ConfiguredPidTool extends AbstractTool
{

    /**
     * Display the title of the tool on the welcome screen.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return sprintf('%s (%s)',
            LocalizationUtility::translate('tool.configured_pid', 'vidi'),
            $this->getModulePidService()->getConfiguredNewRecordPid()
        );
    }

    /**
     * Display the description of the tool in the welcome screen.
     *
     * @return string
     */
    public function getDescription(): string
    {
        $templateNameAndPath = 'EXT:vidi/Resources/Private/Standalone/Tool/ConfiguredPid/Launcher.html';
        $view = $this->initializeStandaloneView($templateNameAndPath);
        $view->assignMultiple([
            'sitePath' => Environment::getPublicPath() . '/',
            'dataType' => $this->getModuleLoader()->getDataType(),
            'configuredPid' => $this->getModulePidService()->getConfiguredNewRecordPid(),
            'errors' => $this->getModulePidService()->validateConfiguredPid(),
        ]);

        return $view->render();
    }

    /**
     * Do the job
     *
     * @param array $arguments
     * @return string
     */
    public function work(array $arguments = array()): string
    {
        return '';
    }

    /**
     * Tell whether the tools should be displayed according to the context.
     *
     * @return bool
     */
    public function isShown(): bool
    {
        return $this->getBackendUser()->isAdmin();
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return \Fab\Vidi\Module\ModuleLoader|object
     */
    protected function getModuleLoader(): \Fab\Vidi\Module\ModuleLoader
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleLoader::class);
    }

    /**
     * @return ModulePidService|object
     */
    public function getModulePidService()
    {
        /** @var ModulePidService $modulePidService */
        return GeneralUtility::makeInstance(ModulePidService::class);
    }

}

