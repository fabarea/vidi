<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = [
    'grid' => [
        'showFields' => '*', // @todo implement me!
        'hideFields' => '', // @todo implement me!
        'columns' => [
            '__checkbox' => [
                'renderer' => new Fab\Vidi\Grid\CheckBoxComponent(),
            ],
            'uid' => [
                'visible' => FALSE,
                'label' => 'Id',
                'width' => '5px',
            ],
            'header' => [
                'editable' => TRUE,
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/tt_content.xlf:header',
            ],
            'tstamp' => [
                'visible' => FALSE,
                'format' => 'Fab\Vidi\Formatter\Date',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:tstamp',
            ],
            'crdate' => [
                'visible' => FALSE,
                'format' => 'Fab\Vidi\Formatter\Date',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:crdate',
            ],
            'hidden' => [
                'renderer' => 'Fab\Vidi\Grid\VisibilityRenderer',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active',
                'width' => '3%',
            ],
            '__buttons' => [
                'renderer' => new Fab\Vidi\Grid\ButtonGroupComponent(),
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['tt_content'], $tca);
