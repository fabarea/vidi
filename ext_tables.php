<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Fab\Vidi\Module\ModuleLoader;
use Fab\Vidi\Backend\LanguageFileGenerator;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use Fab\Vidi\Processor\ContentObjectProcessor;
use Fab\Vidi\Processor\MarkerProcessor;
use Fab\Vidi\Tool\ToolRegistry;
use Fab\Vidi\Tool\ModulePreferencesTool;
use Fab\Vidi\Tool\RelationAnalyserTool;
use Fab\Vidi\Tool\ConfiguredPidTool;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

call_user_func(function () {
    // Check from Vidi configuration what default module should be loaded.
    // Make sure the class exists to avoid a Runtime Error

    // Add content main module before 'user'
    if (!isset($GLOBALS['TBE_MODULES']['content'])) {
        // Position module "content" after module "user" manually. No API is available for that, it seems...
        $modules = [];
        foreach ($GLOBALS['TBE_MODULES'] as $key => $val) {
            if ($key === 'user') {
                $modules['content'] = '';
            }
            $modules[$key] = $val;
        }
        $GLOBALS['TBE_MODULES'] = $modules;

        // Register "data management" module.
        ExtensionManagementUtility::addModule(
            'content',
            '',
            '',
            '',
            [
                'name' => 'content',
                'access' => 'user,group',
                'labels' => [
                    'll_ref' => 'LLL:EXT:vidi/Resources/Private/Language/content_module.xlf',
                ],
            ]
        );
    }

    $configuration = GeneralUtility::makeInstance(
        ExtensionConfiguration::class
    )->get('vidi');

    $pids = GeneralUtility::trimExplode(',', $configuration['default_pid'], true);
    $defaultPid = array_shift($pids);
    $defaultPids = [];
    foreach ($pids as $dataTypeAndPid) {
        $parts = GeneralUtility::trimExplode(':', $dataTypeAndPid);
        if (count($parts) === 2) {
            $defaultPids[$parts[0]] = $parts[1];
        }
    }

    // Loop around the data types and register them to be displayed within a BE module.
    if ($configuration['data_types']) {
        $dataTypes = GeneralUtility::trimExplode(',', $configuration['data_types'], true);
        foreach ($dataTypes as $dataType) {
            /** @var ModuleLoader $moduleLoader */
            $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class, $dataType);

            // Special case already defined in Vidi.
            if ($dataType === 'fe_users') {
                $languageFile = 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf';
                $icon = 'EXT:vidi/Resources/Public/Images/fe_users.svg';
            } elseif ($dataType === 'fe_groups') {
                $languageFile = 'LLL:EXT:vidi/Resources/Private/Language/fe_groups.xlf';
                $icon = 'EXT:vidi/Resources/Public/Images/fe_groups.svg';
            } else {
                /** @var LanguageFileGenerator $languageService */
                $languageService = GeneralUtility::makeInstance(LanguageFileGenerator::class);
                $languageFile = $languageService->generate($dataType);
                $icon = '';
            }

            $pid = $defaultPids[$dataType] ?? $defaultPid;

            /** @var ModuleLoader $moduleLoader */
            $moduleLoader->setIcon($icon)
                ->setModuleLanguageFile($languageFile)
                ->setDefaultPid($pid)
                ->register();
        }
    }

    // Possible Static TS loading
    if (true === isset($configuration['autoload_typoscript']) && false === (bool)$configuration['autoload_typoscript']) {
        ExtensionManagementUtility::addStaticFile('vidi', 'Configuration/TypoScript', 'Vidi: versatile and interactive display');
    }

    // Register List2 only if beta feature is enabled.
    // @todo let see what we do with that
    #if ($configuration['activate_beta_features']) {
    #	$labelFile = 'LLL:EXT:vidi/Resources/Private/Language/locallang_module.xlf';
    #
    #	if (!$configuration['hide_module_list']) {
    #		$labelFile = 'LLL:EXT:vidi/Resources/Private/Language/locallang_module_transitional.xlf';
    #	}
    #
    #	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    #		'vidi',
    #		'web', // Make module a submodule of 'web'
    #		'm1', // Submodule key
    #		'after:list', // Position
    #		array(
    #			'Content' => 'index, list, delete, update, edit, copy, move, localize, sort, copyClipboard, moveClipboard',
    #			'Tool' => 'welcome, work',
    #			'Facet' => 'autoSuggest, autoSuggests',
    #			'Selection' => 'edit, update, create, delete, list, show',
    #			'UserPreferences' => 'save',
    #			'Clipboard' => 'save, flush, show',
    #		), array(
    #			'access' => 'user,group',
    #			'icon' => 'EXT:vidi/Resources/Public/Images/list.png',
    #			'labels' => $labelFile,
    #		)
    #	);
    #}
    #if ($configuration['hide_module_list']) {
    #
    #	// Default User TSConfig to be added in any case.
    #	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
    #
    #		# Hide the module in the BE.
    #		options.hideModules.web := addToList(list)
    #	');
    #}

    /** @var $signalSlotDispatcher \TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
    $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);

    // Connect "processContentData" signal slot with the "ContentObjectProcessor".
    $signalSlotDispatcher->connect(
        'Fab\Vidi\Controller\Backend\ContentController',
        'processContentData',
        ContentObjectProcessor::class,
        'processRelations',
        true
    );

    // Connect "processContentData" signal with the "MarkerProcessor".
    $signalSlotDispatcher->connect(
        'Fab\Vidi\Controller\Backend\ContentController',
        'processContentData',
        MarkerProcessor::class,
        'processMarkers',
        true
    );

    // Register default Tools for Vidi.
    ToolRegistry::getInstance()->register('*', ModulePreferencesTool::class);
    ToolRegistry::getInstance()->register('*', RelationAnalyserTool::class);
    ToolRegistry::getInstance()->register('*', ConfiguredPidTool::class);

    // Add new sprite icon.
    $icons = [
        'go' => 'EXT:vidi/Resources/Public/Images/bullet_go.png',
        'query' => 'EXT:vidi/Resources/Public/Images/drive_disk.png',
    ];
    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    foreach ($icons as $key => $icon) {
        $iconRegistry->registerIcon(
            'extensions-vidi-' . $key,
            BitmapIconProvider::class,
            [
                'source' => $icon
            ]
        );
    }
    unset($iconRegistry);
});
