<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Setting up scripts that can be run from the cli_dispatch.phpsh script.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\CMS\Vidi\Command\VidiCommandController';

// Override classes for the Object Manager
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Backend\View\ModuleMenuView'] = array(
	'className' => 'TYPO3\CMS\Vidi\Override\Backend\View\ModuleMenuView'
);
?>