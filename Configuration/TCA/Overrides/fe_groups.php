<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = [
    'grid' => [
        'facets' => [
            'uid',
            'title',
            'description',
        ],
        'vidi' => [
            // Special case when the field name does not follow the conventions.
            // Vidi needs a bit of help to find the equivalence fieldName <-> propertyName.
            'mappings' => [
                'lockToDomain' => 'lockToDomain',
                'TSconfig' => 'tsConfig',
                'felogin_redirectPid' => 'feLoginRedirectPid',
            ],
        ],
        'columns' => [
            '__checkbox' => [
                'renderer' => new \Fab\Vidi\Grid\CheckBoxRenderer(),
            ],
            'uid' => [
                'visible' => false,
                'label' => 'Id',
                'width' => '5px',
            ],
            'title' => [
                'visible' => true,
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_groups.xlf:title',
                'editable' => true,
            ],
            'tstamp' => [
                'visible' => false,
                'format' => 'Fab\Vidi\Formatter\Date',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:tstamp',
            ],
            'crdate' => [
                'visible' => false,
                'format' => 'Fab\Vidi\Formatter\Date',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:crdate',
            ],
            'hidden' => [
                'renderer' => 'Fab\Vidi\Grid\VisibilityRenderer',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:visibility_abbreviation',
                'width' => '3%',
            ],
            '__buttons' => [
                'renderer' => new \Fab\Vidi\Grid\ButtonGroupRenderer(),
            ],
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['fe_groups'], $tca);