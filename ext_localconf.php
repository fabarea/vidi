<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get('vidi');

    if (false === isset($configuration['autoload_typoscript']) || true === (bool)$configuration['autoload_typoscript']) {

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            'vidi',
            'constants',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:vidi/Configuration/TypoScript/constants.txt">'
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            'vidi',
            'setup',
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:vidi/Configuration/TypoScript/setup.txt">'
        );
    }

    // Initialize generic Vidi modules after the TCA is loaded.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'Fab\Vidi\Configuration\VidiModulesAspect';

    // Initialize generic grid TCA for all data types
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'Fab\Vidi\Configuration\TcaGridAspect';

    // cache configuration, see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/CachingFramework/Configuration/Index.html#cache-configurations
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['vidi']['frontend'] = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['vidi']['groups'] = array('all', 'vidi');
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['vidi']['options']['defaultLifetime'] = 2592000;
});