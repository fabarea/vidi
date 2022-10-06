<?php

use Fab\Vidi\Facet\PageFacet;
use Fab\Vidi\Grid\CheckBoxRenderer;
use Fab\Vidi\Grid\ButtonGroupRenderer;
use TYPO3\CMS\Core\Utility\ArrayUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

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
            PageFacet::class => [
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:facet.pid'
            ]
        ],
        'columns' => [
            '__checkbox' => [
                'renderer' => CheckBoxRenderer::class,
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
            #'hidden' => [
            #    'renderer' => 'Fab\Vidi\Grid\VisibilityRenderer',
            #    'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:visibility_abbreviation',
            #    'width' => '3%',
            #],
            '__buttons' => [
                'renderer' => ButtonGroupRenderer::class,
            ],
        ]
    ]
];

ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['fe_groups'], $tca);
