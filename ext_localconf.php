<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['setup'] = unserialize($_EXTCONF);

if (FALSE === isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['setup']['autoload']) || TRUE === (boolean) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['setup']['autoload']) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'constants',
		'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:vidi/Configuration/TypoScript/constants.txt">'
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'setup',
		'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:vidi/Configuration/TypoScript/setup.txt">'
	);
}

// Configure commands that can be run from the cli_dispatch.phpsh script.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\CMS\Vidi\Command\VidiCommandController';

// Initialize generic Vidi modules after the TCA is loaded.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'EXT:vidi/Classes/Configuration/VidiModulesAspect.php:TYPO3\CMS\Vidi\Configuration\VidiModulesAspect';

// Initialize generic grid TCA for all data types
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'EXT:vidi/Classes/Configuration/TcaGridAspect.php:TYPO3\CMS\Vidi\Configuration\TcaGridAspect';