<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = array(
	'grid' => array(
		'facets' => array(
			'uid',
			'title',
			'description',
		),
		'vidi' => array(
			// Special case when the field name does not follow the conventions.
			// Vidi needs a bit of help to find the equivalence fieldName <-> propertyName.
			'mappings' => array(
				'lockToDomain' => 'lockToDomain',
				'TSconfig' => 'tsConfig',
				'felogin_redirectPid' => 'feLoginRedirectPid',
			),
		),
		'columns' => array(
			'__checkbox' => array(
				'renderer' => new \Fab\Vidi\Grid\CheckBoxComponent(),
			),
			'uid' => array(
				'visible' => FALSE,
				'label' => 'Id',
				'width' => '5px',
			),
			'title' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_groups.xlf:title',
				'editable' => TRUE,
			),
			'tstamp' => array(
				'visible' => FALSE,
				'format' => 'date',
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:tstamp',
			),
			'crdate' => array(
				'visible' => FALSE,
				'format' => 'date',
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:crdate',
			),
			'hidden' => array(
				'renderer' => 'Fab\Vidi\Grid\VisibilityRenderer',
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:visibility_abbreviation',
				'width' => '3%',
			),
			'__buttons' => array(
				'renderer' => new \Fab\Vidi\Grid\ButtonGroupComponent(),
			),
		)
	)
);

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['fe_groups'], $tca);