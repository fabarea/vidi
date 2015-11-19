<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

return [
    'ctrl' => [
        'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:selection',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'hideTable' => TRUE,
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
        '1' => ['showitem' => 'hidden;;1, type, name, data_type, query'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'visibility' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:vidi/Resources/Private/Language/tx_vidi_selection.xlf:visibility',
            'config' => [
                'type' => 'select',
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