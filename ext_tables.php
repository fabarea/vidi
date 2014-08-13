<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Check from Vidi configuration what default module should be loaded.
// Make sure the class exists to avoid a Runtime Error
if (TYPO3_MODE == 'BE' && class_exists('TYPO3\CMS\Vidi\Module\ModuleLoader')) {

	// Register Selection table
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_vidi_domain_model_selection');

	// Add sprite icons.
	\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
		array(
			'selection' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('vidi') . 'Resources/Public/Images/tx_vidi_domain_model_selection.png',
		),
		'vidi'
	);

	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

	/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
	$configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
	$configuration = $configurationUtility->getCurrentConfiguration($_EXTKEY);

	// Loop around the data types and register them to be displayed within a BE module.
	if ($configuration['data_types']['value']) {

		/** @var \TYPO3\CMS\Vidi\Module\ModuleLoader $moduleLoader */
		$moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');

		$dataTypes = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['data_types']['value']);
		foreach ($dataTypes as $dataType) {

			/** @var \TYPO3\CMS\Vidi\Module\ModuleLoader $moduleLoader */
			$moduleLoader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader', $dataType);
			$moduleLoader->setIcon(sprintf('EXT:vidi/Resources/Public/Images/%s.png', $dataType))
				->setModuleLanguageFile(sprintf('LLL:EXT:vidi/Resources/Private/Language/%s.xlf', $dataType))
				->addJavaScriptFiles(array(sprintf('EXT:vidi/Resources/Public/JavaScript/%s.js', $dataType)))
				->setDefaultPid($configuration['default_pid']['value'])
				->register();
		}
	}

	// Register List2 only if beta feature is enabled.
	if ($configuration['activate_beta_features']['value']) {
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
			$_EXTKEY, 'web', // Make newsletter module a submodule of 'user'
			'm1', // Submodule key
			'after:list', // Position
			array(
				'Content' => 'index, list, delete, update, edit',
				'FacetValue' => 'list',
			), array(
				'access' => 'user,group',
				'icon' => 'EXT:vidi/Resources/Public/Images/list.png',
				'labels' => 'LLL:EXT:vidi/Resources/Private/Language/locallang_module.xlf',
			)
		);
	}

	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

	/** @var $signalSlotDispatcher \TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
	$signalSlotDispatcher = $objectManager->get('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');


	// Connect "processContentData" signal slot with the "ContentObjectProcessor".
	$signalSlotDispatcher->connect(
		'TYPO3\CMS\Vidi\Controller\Backend\ContentController',
		'processContentData',
		'TYPO3\CMS\Vidi\Processor\ContentObjectProcessor',
		'processRelations',
		TRUE
	);

	// Connect "processContentData" signal with the "MarkerProcessor".
	$signalSlotDispatcher->connect(
		'TYPO3\CMS\Vidi\Controller\Backend\ContentController',
		'processContentData',
		'TYPO3\CMS\Vidi\Processor\MarkerProcessor',
		'processMarkers',
		TRUE
	);
}

// Add new sprite icon.
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
	array(
		'go' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Images/bullet_go.png',
	),
	$_EXTKEY
);