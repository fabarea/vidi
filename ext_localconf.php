<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Setting up scripts that can be run from the cli_dispatch.phpsh script.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\CMS\Vidi\Command\VidiCommandController';

// Override classes for the Object Manager. Can be removed once 6.1 has reached EOL.
if (version_compare(TYPO3_branch, '6.1', '<=')) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Backend\View\ModuleMenuView'] = array(
		'className' => 'TYPO3\CMS\Vidi\Override\Backend\View\ModuleMenuView'
	);
}
?>