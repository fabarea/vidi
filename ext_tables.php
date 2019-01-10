<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    // Check from Vidi configuration what default module should be loaded.
    // Make sure the class exists to avoid a Runtime Error
    if (TYPO3_MODE === 'BE') {

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
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
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

        $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('vidi');

        $pids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['default_pid'], true);
        $defaultPid = array_shift($pids);
        $defaultPids = [];
        foreach ($pids as $dataTypeAndPid) {
            $parts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(':', $dataTypeAndPid);
            if (count($parts) === 2) {
                $defaultPids[$parts[0]] = $parts[1];
            }
        }

        // Loop around the data types and register them to be displayed within a BE module.
        if ($configuration['data_types']) {

            $dataTypes = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['data_types'], true);
            foreach ($dataTypes as $dataType) {


                /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
                $moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Fab\Vidi\Module\ModuleLoader::class, $dataType);

                // Special case already defined in Vidi.
                if ($dataType === 'fe_users') {
                    $languageFile = 'LLL:EXT:vidi/Resources/Private/Language/fe_users.xlf';
                    $icon = 'EXT:core/Resources/Public/Icons/T3Icons/status/status-user-frontend.svg';
                    $icon = 'EXT:vidi/Resources/Public/Images/fe_users.svg';
                } elseif ($dataType === 'fe_groups') {
                    $languageFile = 'LLL:EXT:vidi/Resources/Private/Language/fe_groups.xlf';
                    $icon = 'EXT:core/Resources/Public/Icons/T3Icons/status/status-user-group-frontend.svg';
                    $icon = 'EXT:vidi/Resources/Public/Images/fe_groups.svg';
                } else {
                    /** @var \Fab\Vidi\Backend\LanguageFileGenerator $languageService */
                    $languageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Fab\Vidi\Backend\LanguageFileGenerator::class);
                    $languageFile = $languageService->generate($dataType);
                    $icon = '';
                }

                $pid = isset($defaultPids[$dataType]) ? $defaultPids[$dataType] : $defaultPid;

                /** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
                $moduleLoader->setIcon($icon)
                    ->setModuleLanguageFile($languageFile)
                    ->setDefaultPid($pid)
                    ->register();
            }
        }

        // Possible Static TS loading
        if (true === isset($configuration['autoload_typoscript']) && false === (bool)$configuration['autoload_typoscript']) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('vidi', 'Configuration/TypoScript', 'Vidi: versatile and interactive display');
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

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        /** @var $signalSlotDispatcher \TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
        $signalSlotDispatcher = $objectManager->get('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');

        // Connect "processContentData" signal slot with the "ContentObjectProcessor".
        $signalSlotDispatcher->connect(
            'Fab\Vidi\Controller\Backend\ContentController',
            'processContentData',
            'Fab\Vidi\Processor\ContentObjectProcessor',
            'processRelations',
            true
        );

        // Connect "processContentData" signal with the "MarkerProcessor".
        $signalSlotDispatcher->connect(
            'Fab\Vidi\Controller\Backend\ContentController',
            'processContentData',
            'Fab\Vidi\Processor\MarkerProcessor',
            'processMarkers',
            true
        );

        // Register default Tools for Vidi.
        \Fab\Vidi\Tool\ToolRegistry::getInstance()->register('*', 'Fab\Vidi\Tool\ModulePreferencesTool');
        \Fab\Vidi\Tool\ToolRegistry::getInstance()->register('*', 'Fab\Vidi\Tool\RelationAnalyserTool');
    }

    // Add new sprite icon.
    $icons = [
        'go' => 'EXT:' . $_EXTKEY . '/Resources/Public/Images/bullet_go.png',
        'query' => 'EXT:' . $_EXTKEY . '/Resources/Public/Images/drive_disk.png',
    ];
    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($icons as $key => $icon) {
        $iconRegistry->registerIcon('extensions-' . $_EXTKEY . '-' . $key,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            [
                'source' => $icon
            ]
        );
    }
    unset($iconRegistry);
});
