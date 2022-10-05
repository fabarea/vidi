<?php

namespace Fab\Vidi\Configuration;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use Fab\Vidi\Module\ModuleLoader;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
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
        /** @var ModuleLoader $moduleLoader */
        $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);


        $configuration = GeneralUtility::makeInstance(
            ExtensionConfiguration::class
        )->get('vidi');

        foreach (GeneralUtility::trimExplode(',', $configuration['data_types'], true) as $dataType) {
            if (!$moduleLoader->isRegistered($dataType)) {
                $moduleLoader->setDataType($dataType)
                    #->isShown(false)
                    ->register();
            }
        }
    }
}
