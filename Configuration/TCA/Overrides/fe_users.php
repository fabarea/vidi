<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = array(
	'ctrl' => array(
		// By default "searchFields" has many fields which has a performance cost when dealing with large data-set.
		// Override search field for performance reason.
		// To restore default values, just replace with this: $GLOBALS['TCA']['fe_users']['ctrl']['searchFields'] . ',usergroup',
		'searchFields' => 'username, first_name, last_name, usergroup',
	),
	'vidi' => array(
		// Special case when the field name does not follow the conventions.
		// Vidi needs a bit of help to find the equivalence fieldName <-> propertyName.
		'mappings' => array(
			'lockToDomain' => 'lockToDomain',
			'TSconfig' => 'tsConfig',
			'felogin_redirectPid' => 'feLoginRedirectPid',
			'felogin_forgotHash' => 'feLoginForgotHash',
		),
	),
	'grid' => array(
		'export' => array(
			'excluded_fields' => 'lockToDomain, TSconfig, felogin_redirectPid, felogin_forgotHash',
			'include_files' => FALSE,
		),
		'facets' => array(
			'uid',
			'username',
			'name',
			'first_name',
			'last_name',
			'middle_name',
			'address',
			'telephone',
			'fax',
			'email',
			'title',
			'zip',
			'city',
			'country',
			'company',
			'usergroup',
			new \TYPO3\CMS\Vidi\Facet\StandardFacet(
				'disable',
				'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active',
				array(
					'0' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active.0',
					'1' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active.1'
				)
			),
		),
		'columns' => array(
			'__checkbox' => array(
				'renderer' => new TYPO3\CMS\Vidi\Grid\CheckBoxComponent(),
			),
			'uid' => array(
				'visible' => FALSE,
				'label' => 'Id',
				'width' => '5px',
			),
			'username' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:username',
				'editable' => TRUE,
			),
			'name' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:name',
				'editable' => TRUE,
			),
			'email' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:email',
				'editable' => TRUE,
			),
			'usergroup' => array(
				'visible' => TRUE,
				'renderers' => array(
					'TYPO3\CMS\Vidi\Grid\RelationEditRenderer',
					'TYPO3\CMS\Vidi\Grid\RelationRenderer',
				),
				'editable' => TRUE,
				'sortable' => FALSE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:usergroup',
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
			'disable' => array(
				'renderer' => 'TYPO3\CMS\Vidi\Grid\VisibilityRenderer',
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active',
				'width' => '3%',
			),
			'__buttons' => array(
				'renderer' => new TYPO3\CMS\Vidi\Grid\ButtonGroupComponent(),
			),
		),
	),
);

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['fe_users'], $tca);
