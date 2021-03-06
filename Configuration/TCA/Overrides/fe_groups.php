<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = [
    'vidi' => [
        // Special case when the field name does not follow the conventions.
        // Vidi needs a bit of help to find the equivalence fieldName <-> propertyName.
        'mappings' => [
            'lockToDomain' => 'lockToDomain',
            'TSconfig' => 'tsConfig',
            'felogin_redirectPid' => 'feLoginRedirectPid',
        ],
    ],
    'grid' => [
        'facets' => [
            'uid',
            'title',
            'description',
            \Fab\Vidi\Facet\PageFacet::class => [
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:facet.pid'
            ]
        ],
        'columns' => [
            '__checkbox' => [
                'renderer' => \Fab\Vidi\Grid\CheckBoxRenderer::class,
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
                'renderer' => \Fab\Vidi\Grid\ButtonGroupRenderer::class,
            ],
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['fe_groups'], $tca);