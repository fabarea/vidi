<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = [
    'ctrl' => [
        // By default "searchFields" has many fields which has a performance cost when dealing with large data-set.
        // Override search field for performance reason.
        // To restore default values, just replace with this: $GLOBALS['TCA']['fe_users']['ctrl']['searchFields'] . ',usergroup',
        'searchFields' => 'username, first_name, last_name, usergroup',
    ],
    'vidi' => [
        // Special case when the field name does not follow the conventions.
        // Vidi needs a bit of help to find the equivalence fieldName <-> propertyName.
        'mappings' => [
            'lockToDomain' => 'lockToDomain',
            'TSconfig' => 'tsConfig',
            'felogin_redirectPid' => 'feLoginRedirectPid',
            'felogin_forgotHash' => 'feLoginForgotHash',
        ],
    ],
    'grid' => [
        'excluded_fields' => 'lockToDomain, TSconfig, felogin_redirectPid, felogin_forgotHash, auth_token',
        'export' => [
            'include_files' => FALSE,
        ],
        'facets' => [
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
            new \Fab\Vidi\Facet\StandardFacet(
                'disable',
                'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active',
                [
                    '0' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active.0',
                    '1' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active.1'
                ]
            ),
        ],
        'columns' => [
            '__checkbox' => [
                'renderer' => new Fab\Vidi\Grid\CheckBoxRenderer(),
            ],
            'uid' => [
                'visible' => FALSE,
                'label' => 'Id',
                'width' => '5px',
            ],
            'username' => [
                'visible' => TRUE,
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:username',
                'editable' => TRUE,
            ],
            'name' => [
                'visible' => TRUE,
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:name',
                'editable' => TRUE,
            ],
            'email' => [
                'visible' => TRUE,
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:email',
                'editable' => TRUE,
            ],
            'usergroup' => [
                'visible' => TRUE,
                'renderers' => [
                    'Fab\Vidi\Grid\RelationEditRenderer',
                    'Fab\Vidi\Grid\RelationRenderer',
                ],
                'editable' => TRUE,
                'sortable' => FALSE,
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf:usergroup',
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
            'disable' => [
                'renderer' => 'Fab\Vidi\Grid\VisibilityRenderer',
                'label' => 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:active',
                'width' => '3%',
            ],
            '__buttons' => [
                'renderer' => new Fab\Vidi\Grid\ButtonGroupRenderer(),
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['fe_users'], $tca);
