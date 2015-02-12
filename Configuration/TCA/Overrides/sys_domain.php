<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = array(
	'vidi' => array(
		'mappings' => array(
			'domainName' => 'domainName'
		)
	)
);

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['sys_domain'], $tca);
