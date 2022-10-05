<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:phpdisplay/Resources/Private/Language/locallang_db.xml:tx_phpdisplay_displays',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'hideTable' => true,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'type,name,data_type',
        'typeicon_classes' => [
            'default' => 'extensions-vidi-selection',
        ],
    ],
    'types' => [
        '1' => ['showitem' => 'hidden,--palette--;;1,type,name,data_type,query'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'visibility' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:visibility',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:visibility.everyone', 0],
                    ['LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:visibility.private', 1],
                    ['LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:visibility.admin_only', 2],
                ],
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 1,
            ],
        ],
        'name' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'data_type' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:data_type',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'query' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:query',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 5,
            ],
        ],
        'speaking_query' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:speaking_query',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 5,
            ],
        ],
    ],
];
