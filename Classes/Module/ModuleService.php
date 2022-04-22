<?php
namespace Fab\Vidi\Module;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Service\DataService;
use Fab\Vidi\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Fab\Vidi\Tca\Tca;

/**
 * Service related to data type (AKA tablename)
 * @deprecated this class is not used anymore
 */
class ModuleService implements SingletonInterface
{

    /**
     * @var array
     */
    protected $storage = [];

    /**
     * Returns a class instance
     *
     * @return \Fab\Vidi\Module\ModuleService|object
     */
    static public function getInstance()
    {
        return GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleService::class);
    }

    /**
     * Fetch all modules to be displayed on the current pid.
     *
     * @return array
     */
    public function getModulesForCurrentPid(): array
    {
        $pid = $this->getModuleLoader()->getCurrentPid();
        return $this->getModulesForPid($pid);
    }

    /**
     * Fetch all modules displayed given a pid.
     *
     * @param int $pid
     * @return array
     */
    public function getModulesForPid($pid = null): array
    {
        if (!isset($this->storage[$pid])) {

            $modules = [];
            foreach ($GLOBALS['TCA'] as $dataType => $configuration) {
                if (Tca::table($dataType)->isNotHidden()) {
                    $record = $this->getDataService()->getRecord(
                        $dataType,
                        [
                            'pid' => $pid
                        ]
                    );
                    if (!empty($record)) {
                        $moduleName = 'Vidi' . GeneralUtility::underscoredToUpperCamelCase($dataType) . 'M1';
                        $title = Tca::table($dataType)->getTitle();
                        $modules[$moduleName] = $title;
                    }
                }
            }
            $this->storage[$pid] = $modules;
        }
        return $this->storage[$pid];
    }

    /**
     * Fetch the first module for the current pid.
     *
     * @return string
     */
    public function getFirstModuleForCurrentPid(): string
    {
        $pid = $this->getModuleLoader()->getCurrentPid();
        return $this->getFirstModuleForPid($pid);
    }

    /**
     * Fetch the module given a pid.
     *
     * @param int $pid
     * @return string
     */
    public function getFirstModuleForPid($pid): string
    {
        $firstModule = '';
        $modules = $this->getModulesForPid($pid);
        if (!empty($modules)) {
            $firstModule = key($modules);
        }

        return $firstModule;
    }

    /**
     * @return object|DataService
     */
    protected function getDataService(): DataService
    {
        return GeneralUtility::makeInstance(DataService::class);
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
}
