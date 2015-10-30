<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Check from Vidi configuration what default module should be loaded.
// Make sure the class exists to avoid a Runtime Error
if (TYPO3_MODE == 'BE') {

	// Add content main module before 'user'
	// There are not API for doing this... ;(
	if (!isset($GLOBALS['TBE_MODULES']['content'])) {
		$modules = array();
		foreach ($GLOBALS['TBE_MODULES'] as $key => $val) {
			if ($key == 'user') {
				$modules['content'] = '';
			}
			$modules[$key] = $val;
		}
		$GLOBALS['TBE_MODULES'] = $modules;
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
			'content',
			'',
			'',
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('vidi') . 'mod/content/');
	}

	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

	/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
	$configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
	$configuration = $configurationUtility->getCurrentConfiguration('vidi');

	// Loop around the data types and register them to be displayed within a BE module.
	if ($configuration['data_types']['value']) {

		$dataTypes = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['data_types']['value']);
		foreach ($dataTypes as $dataType) {

			/** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
			$moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader', $dataType);

			/** @var \Fab\Vidi\Module\ModuleLoader $moduleLoader */
			$moduleLoader->setIcon(sprintf('EXT:vidi/Resources/Public/Images/%s.png', $dataType))
				->setModuleLanguageFile(sprintf('LLL:EXT:vidi/Resources/Private/Language/%s.xlf', $dataType))
				->addJavaScriptFiles(array(sprintf('EXT:vidi/Resources/Public/JavaScript/%s.js', $dataType)))
				->setDefaultPid($configuration['default_pid']['value'])
				->register();
		}
	}

	// Possible Static TS loading
	if (TRUE === isset($configuration['autoload_typoscript']['value']) && FALSE === (bool)$configuration['autoload_typoscript']['value']) {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('vidi', 'Configuration/TypoScript', 'Vidi: versatile and interactive display');
	}

	// Register List2 only if beta feature is enabled.
	if ($configuration['activate_beta_features']['value']) {
		$labelFile = 'LLL:EXT:vidi/Resources/Private/Language/locallang_module.xlf';

		if (!$configuration['hide_module_list']['value']) {
			$labelFile = 'LLL:EXT:vidi/Resources/Private/Language/locallang_module_transitional.xlf';
		}

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
			'vidi',
			'web', // Make module a submodule of 'web'
			'm1', // Submodule key
			'after:list', // Position
			array(
				'Content' => 'index, list, delete, update, edit, copy, move, localize, sort, copyClipboard, moveClipboard',
				'Tool' => 'welcome, work',
				'Facet' => 'autoSuggest, autoSuggests',
				'Selection' => 'edit, update, create, delete, list, show',
				'UserPreferences' => 'save',
				'Clipboard' => 'save, flush, show',
			), array(
				'access' => 'user,group',
				'icon' => 'EXT:vidi/Resources/Public/Images/list.png',
				'labels' => $labelFile,
			)
		);
	}

	if ($configuration['hide_module_list']['value']) {

		// Default User TSConfig to be added in any case.
		TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('

			# Hide the module in the BE.
			options.hideModules.web := addToList(list)
		');
	}

	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

	/** @var $signalSlotDispatcher \TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
	$signalSlotDispatcher = $objectManager->get('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');

	// Connect "processContentData" signal slot with the "ContentObjectProcessor".
	$signalSlotDispatcher->connect(
		'Fab\Vidi\Controller\Backend\ContentController',
		'processContentData',
		'Fab\Vidi\Processor\ContentObjectProcessor',
		'processRelations',
		TRUE
	);

	// Connect "processContentData" signal with the "MarkerProcessor".
	$signalSlotDispatcher->connect(
		'Fab\Vidi\Controller\Backend\ContentController',
		'processContentData',
		'Fab\Vidi\Processor\MarkerProcessor',
		'processMarkers',
		TRUE
	);

	// Register default Tools for Vidi.
	\Fab\Vidi\Tool\ToolRegistry::getInstance()->register('*', 'Fab\Vidi\Tool\ModulePreferencesTool');
	\Fab\Vidi\Tool\ToolRegistry::getInstance()->register('*', 'Fab\Vidi\Tool\RelationAnalyserTool');
}

// Add new sprite icon.
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
	array(
		'go' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('vidi') . 'Resources/Public/Images/bullet_go.png',
		'query' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('vidi') . 'Resources/Public/Images/drive_disk.png',
	),
	'vidi'
);