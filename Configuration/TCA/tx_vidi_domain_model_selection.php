<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

return array(
	'ctrl' => array(
		'matches'	=> 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:selection',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
		),
		'searchFields' => 'type,name,data_type',
		'typeicon_classes' => array(
			'default' => 'extensions-vidi-selection',
		),
	),
	'types' => array(
		'1' => array('showitem' => 'hidden;;1, type, name, data_type, matches'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:type',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:type.public', 1),
					array('LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:type.private', 2),
				),
				'size' => 1,
				'maxitems' => 1,
				'minitems' => 1,
			),
		),
		'name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'data_type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:data_type',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'matches' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:matches',
			'config' => array(
				'type' => 'text',
				'rows' => 5,
				'cols' => 5,
			),
		),
	),
);