<?php
namespace Fab\Vidi\Configuration;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Initialize Vidi modules
 */
class VidiModulesAspect implements TableConfigurationPostProcessingHookInterface
{

    /**
     * Initialize and populate TBE_MODULES_EXT with default data.
     *
     * @return void
     */
    public function processData()
    {

        /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
        $moduleLoader = GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');

        // Each data data can be displayed in a Vidi module
        foreach ($GLOBALS['TCA'] as $dataType => $configuration) {
            if (!$moduleLoader->isRegistered($dataType)) {

                // @todo some modules have TSConfig configuration for not being displayed. Should be respected!
                $moduleLoader->setDataType($dataType)
                    ->isShown(false)
                    ->register();
            }
        }
    }

}