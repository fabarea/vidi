<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Setting up scripts that can be run from the cli_dispatch.phpsh script.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\CMS\Vidi\Command\VidiCommandController';

?>