<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['vidi']);

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

// Configure commands that can be run from the cli_dispatch.phpsh script.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Fab\Vidi\Command\VidiCommandController';

// Initialize generic Vidi modules after the TCA is loaded.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'Fab\Vidi\Configuration\VidiModulesAspect';

// Initialize generic grid TCA for all data types
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'Fab\Vidi\Configuration\TcaGridAspect';

$TYPO3_CONF_VARS['SYS']['caching']['cacheConfigurations']['vidi'] = array(
    'frontend' => 'TYPO3\CMS\Core\Cache\Frontend\VariableFrontend',
    'options' => array(
        'defaultLifetime' => 2592000, // => 1 month
    ),
    'groups' => array('all', 'vidi')
);
