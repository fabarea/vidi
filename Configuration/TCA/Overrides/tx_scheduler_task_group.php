<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$tca = array(
        'vidi' => array(
                'mappings' => array(
                        'groupName' => 'groupName'
                )
        )
);

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['tx_scheduler_task_group'],$tca);
