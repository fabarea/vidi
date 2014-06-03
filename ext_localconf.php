<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Configure commands that can be run from the cli_dispatch.phpsh script.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\CMS\Vidi\Command\VidiCommandController';

// Initialize generic Vidi modules after the TCA is loaded.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'EXT:vidi/Classes/Configuration/VidiModulesAspect.php:TYPO3\CMS\Vidi\Configuration\VidiModulesAspect';

// Initialize generic grid TCA for all data types
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['extTablesInclusion-PostProcessing'][] = 'EXT:vidi/Classes/Configuration/TcaGridAspect.php:TYPO3\CMS\Vidi\Configuration\TcaGridAspect';