<?php

use Fab\Vidi\Controller\ClipboardController;
use Fab\Vidi\Controller\ContentController;
use Fab\Vidi\Controller\FacetController;
use Fab\Vidi\Controller\SelectionController;
use Fab\Vidi\Controller\ToolController;
use Fab\Vidi\Controller\UserPreferencesController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backend modules for every data type configured in extension settings.
 *
 * TYPO3 13 loads this file while compiling the dependency injection container.
 * Extension configuration is already available in $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'].
 * A static list cannot cover the data types registered by other extensions.
 */
$configuredDataTypes = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['vidi']['data_types'] ?? 'fe_users,fe_groups';
$dataTypes = GeneralUtility::trimExplode(',', (string)$configuredDataTypes, true);

$modules = [
    'content' => [
        'labels' => 'LLL:EXT:vidi/Resources/Private/Language/content_module.xlf',
        'iconIdentifier' => 'actions-database',
        'position' => ['after' => 'web'],
    ],
];

$previousIdentifier = null;
foreach ($dataTypes as $dataType) {
    $identifier = 'content_vidi_' . $dataType . '_m1';
    $modules[$identifier] = [
        'parent' => 'content',
        'position' => $previousIdentifier === null ? ['before' => '*'] : ['after' => $previousIdentifier],
        'access' => 'user,group',
        'path' => '/module/vidi/' . $dataType,
        'iconIdentifier' => match ($dataType) {
            'fe_users' => 'status-user-frontend',
            'fe_groups' => 'status-user-group-frontend',
            default => 'actions-database',
        },
        'labels' => match ($dataType) {
            'fe_users' => 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf',
            'fe_groups' => 'LLL:EXT:vidi/Resources/Private/Language/fe_groups.xlf',
            default => 'LLL:EXT:vidi/Resources/Private/Language/locallang_module.xlf',
        },
        'extensionName' => 'Vidi',
        'navigationComponent' => '@typo3/backend/tree/page-tree-element',
        'controllerActions' => [
            ContentController::class => [
                'index', 'list', 'delete', 'update', 'edit', 'copy', 'move',
                'localize', 'sort', 'copyClipboard', 'moveClipboard',
            ],
            ToolController::class => ['welcome', 'work'],
            FacetController::class => ['autoSuggest', 'autoSuggests'],
            SelectionController::class => ['edit', 'update', 'create', 'delete', 'list', 'show'],
            UserPreferencesController::class => ['save'],
            ClipboardController::class => ['save', 'flush', 'show'],
        ],
    ];
    $previousIdentifier = $identifier;
}

return $modules;
