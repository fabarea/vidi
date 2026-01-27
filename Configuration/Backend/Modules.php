<?php

declare(strict_types=1);

use Fab\Vidi\Controller\ContentController;

/**
 * Backend module configuration for Vidi extension.
 * This file registers backend modules for the configured data types.
 * 
 * Configured data types: fe_users, fe_groups, tt_address
 * Main module: content
 */
return [
    // Module for fe_users
    'content_vidi_fe_users_m1' => [
        'parent' => 'content',
        'position' => ['after' => 'web_list'],
        'access' => 'user,group',
        'path' => '/module/vidi/fe_users',
        'iconIdentifier' => 'module-vidi',
        'labels' => 'LLL:EXT:vidi/Resources/Private/Language/locallang_mod.xlf',
        'routes' => [
            '_default' => [
                'target' => ContentController::class . '::indexAction',
            ],
        ],
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'inheritNavigationComponentFromMainModule' => true,
    ],
    
    // Module for fe_groups
    'content_vidi_fe_groups_m1' => [
        'parent' => 'content',
        'position' => ['after' => 'content_vidi_fe_users_m1'],
        'access' => 'user,group',
        'path' => '/module/vidi/fe_groups',
        'iconIdentifier' => 'module-vidi',
        'labels' => 'LLL:EXT:vidi/Resources/Private/Language/locallang_mod.xlf',
        'routes' => [
            '_default' => [
                'target' => ContentController::class . '::indexAction',
            ],
        ],
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'inheritNavigationComponentFromMainModule' => true,
    ],
    
    // Module for tt_address
    'content_vidi_tt_address_m1' => [
        'parent' => 'content',
        'position' => ['after' => 'content_vidi_fe_groups_m1'],
        'access' => 'user,group',
        'path' => '/module/vidi/tt_address',
        'iconIdentifier' => 'module-vidi',
        'labels' => 'LLL:EXT:vidi/Resources/Private/Language/locallang_mod.xlf',
        'routes' => [
            '_default' => [
                'target' => ContentController::class . '::indexAction',
            ],
        ],
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'inheritNavigationComponentFromMainModule' => true,
    ],
];
