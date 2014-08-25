<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = array(
	'grid' => array(
		'facets' => array(
			'uid',
			'title',
			'description',
		),
		'columns' => array(
			'__checkbox' => array(
				'width' => '5px',
				'sortable' => FALSE,
				'html' => '<input type="checkbox" class="checkbox-row-top"/>',
			),
			'uid' => array(
				'visible' => FALSE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_groups.xlf:uid',
				'width' => '5px',
			),
			'title' => array(
				'visible' => TRUE,
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_groups.xlf:title',
				'editable' => FALSE, // @todo make me "true" after fix for jeditable plugin
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
				'renderer' => 'TYPO3\CMS\Vidi\Grid\VisibilityRenderer',
				'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:visibility_abbreviation',
				'width' => '3%',
			),
			'__buttons' => array(
				'sortable' => FALSE,
				'width' => '70px',
			),
		)
	)
);

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['fe_groups'], $tca);