<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('scheduler')) {
    $tca = [
        'vidi' => [
            'mappings' => [
                'groupName' => 'groupName'
            ]
        ]
    ];

    \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['tx_scheduler_task_group'], $tca);
}
