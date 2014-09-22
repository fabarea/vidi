<?php
namespace TYPO3\CMS\Vidi\Module;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException;
use TYPO3\CMS\Vidi\Service\BackendUserPreferenceService;

/**
 * Service class used in other extensions to register a vidi based backend module.
 */
class ModuleLoader {

	/**
	 * The type of data being listed (which corresponds to a table name in TCA)
	 *
	 * @var string
	 */
	protected $dataType;

	/**
	 * @var string
	 */
	protected $defaultPid;

	/**
	 * @var boolean
	 */
	protected $showPageTree;

	/**
	 * @var bool
	 */
	protected $isShown = TRUE;

	/**
	 * @var string
	 */
	protected $access = Access::USER;

	/**
	 * @var string
	 */
	protected $mainModule = 'user';

	/**
	 * @var string
	 */
	protected $position = '';

	/**
	 * @var string
	 */
	protected $icon = 'EXT:vidi/ext_icon.gif';

	/**
	 * @var string
	 */
	protected $moduleLanguageFile = 'LLL:EXT:vidi/Resources/Private/Language/locallang_module.xlf';

	/**
	 * The module key such as m1, m2.
	 *
	 * @var string
	 */
	protected $moduleKey = 'm1';

	/**
	 * @var string[]
	 */
	protected $additionalJavaScriptFiles = array();

	/**
	 * @var string[]
	 */
	protected $additionalStyleSheetFiles = array();

	/**
	 * @var array
	 */
	protected $components = array(
		ModulePosition::DOC_HEADER => array(
			ModulePosition::TOP => array(
				ModulePosition::LEFT => array(),
				ModulePosition::RIGHT => array(
					'TYPO3\CMS\Vidi\View\Button\ToolButton',
				),
			),
			ModulePosition::BOTTOM => array(
				ModulePosition::LEFT => array(
					'TYPO3\CMS\Vidi\View\Button\NewButton',
					'TYPO3\CMS\Vidi\ViewHelpers\Link\BackViewHelper',
				),
				ModulePosition::RIGHT => array(),
			),
		),
		ModulePosition::GRID => array(
			ModulePosition::TOP => array(
				'TYPO3\CMS\Vidi\View\Check\PidCheck',
				'TYPO3\CMS\Vidi\View\Check\RelationsCheck',
				'TYPO3\CMS\Vidi\View\Tab\DataTypeTab',
			),
			ModulePosition::BUTTONS => array(
				'TYPO3\CMS\Vidi\View\Button\EditButton',
				'TYPO3\CMS\Vidi\View\Button\DeleteButton',
			),
			ModulePosition::BOTTOM => array(),
		),
		ModulePosition::MENU_MASS_ACTION => array(
			'TYPO3\CMS\Vidi\View\MenuItem\ExportXlsMenuItem',
			'TYPO3\CMS\Vidi\View\MenuItem\ExportXmlMenuItem',
			'TYPO3\CMS\Vidi\View\MenuItem\ExportCsvMenuItem',
			'TYPO3\CMS\Vidi\View\MenuItem\DividerMenuItem',
			'TYPO3\CMS\Vidi\View\MenuItem\MassDeleteMenuItem',
			#'TYPO3\CMS\Vidi\View\MenuItem\MassEditMenuItem',
		),
	);

	/**
	 * @param string $dataType
	 */
	public function __construct($dataType = '') {
		$this->dataType = $dataType;
	}

	/**
	 * Tell whether a module is already registered.
	 *
	 * @param string $dataType
	 * @return bool
	 */
	public function isRegistered($dataType) {
		$internalModuleCode = $this->getInternalModuleCode($dataType);
		return !empty($GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleCode]);
	}

	/**
	 * Compute the internal module code
	 *
	 * @param NULL|string $dataType
	 * @return string
	 */
	protected function getInternalModuleCode($dataType = NULL) {
		if (is_null($dataType)) {
			$dataType = $this->dataType;
		}
		$subModuleName = $dataType . '_' . $this->moduleKey;
		return 'Vidi' . GeneralUtility::underscoredToUpperCamelCase($subModuleName);
	}

	/**
	 * Register the module
	 *
	 * @return void
	 */
	public function register() {

		$internalModuleCode = $this->getInternalModuleCode();

		$GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleCode] = array();
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleCode]['dataType'] = $this->dataType;
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleCode]['defaultPid'] = is_null($this->defaultPid) ? 0 : $this->defaultPid;
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleCode]['additionalJavaScriptFiles'] = $this->additionalJavaScriptFiles;
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleCode]['additionalStyleSheetFiles'] = $this->additionalStyleSheetFiles;
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$internalModuleCode]['components'] = $this->components;

		// Register and displays module in the BE only if told, default is TRUE.
		if ($this->isShown) {

			$moduleConfiguration = array(
				'access' => $this->access,
				'icon' => $this->icon,
				'labels' => $this->moduleLanguageFile,
				'inheritNavigationComponentFromMainModule' => TRUE
			);

			if (!is_null($this->showPageTree)) {
				if ($this->showPageTree) {
					$moduleConfiguration['navigationComponentId'] = 'typo3-pagetree';
				} else {
					$moduleConfiguration['inheritNavigationComponentFromMainModule'] = FALSE;
				}
			}

			ExtensionUtility::registerModule(
				'vidi',
				$this->mainModule,
				$this->dataType . '_' . $this->moduleKey,
				$this->position,
				array(
					'Content' => 'index, list, delete, update, edit, copy, move, localize',
					'Tool' => 'welcome, work',
					'Facet' => 'suggest',
				),
				$moduleConfiguration

			);
		}
	}

	/**
	 * Return the module code for a BE module.
	 *
	 * @return string
	 */
	public function getModuleCode() {
		return GeneralUtility::_GP(Parameter::MODULE);
	}

	/**
	 * Tell whether the current module is the list one.
	 *
	 * @return bool
	 */
	public function copeWithPageTree() {
		return GeneralUtility::_GP(Parameter::MODULE) === 'web_VidiM1';
	}

	/**
	 * Returns the current pid.
	 *
	 * @return bool
	 */
	public function getCurrentPid() {
		return GeneralUtility::_GET(Parameter::PID) > 0 ? (int)GeneralUtility::_GET(Parameter::PID) : 0;
	}

	/**
	 * Return the Vidi module code which is stored in TBE_MODULES_EXT
	 *
	 * @return string
	 */
	public function getVidiModuleCode() {

		if ($this->copeWithPageTree()) {
			$userPreferenceKey = sprintf('Vidi_pid_%s', $this->getCurrentPid());

			if (GeneralUtility::_GP(Parameter::SUBMODULE)) {
				$subModuleCode = GeneralUtility::_GP(Parameter::SUBMODULE);
				BackendUserPreferenceService::getInstance()->set($userPreferenceKey, $subModuleCode);
			} else {

				$defaultModuleCode = BackendUserPreferenceService::getInstance()->get($userPreferenceKey);
				if (empty($defaultModuleCode)) {
					$defaultModuleCode = 'VidiTtContentM1'; // hard-coded submodule
					BackendUserPreferenceService::getInstance()->set($userPreferenceKey, $defaultModuleCode);
				}

				$vidiModules = ModuleService::getInstance()->getModulesForCurrentPid();

				if (empty($vidiModules)) {
					$subModuleCode = $defaultModuleCode;
				} elseif (isset($vidiModules[$defaultModuleCode])) {
					$subModuleCode = $defaultModuleCode;
				} else {
					$subModuleCode = ModuleService::getInstance()->getFirstModuleForPid($this->getCurrentPid());
				}
			}
		} else {
			$moduleCode = $this->getModuleCode();

			// Remove first part which is separated "_"
			$delimiter = strpos($moduleCode, '_') + 1;
			$subModuleCode = substr($moduleCode, $delimiter);
		}

		return $subModuleCode;
	}

	/**
	 * Return the module URL.
	 *
	 * @param array $additionalParameters
	 * @return string
	 */
	public function getModuleUrl(array $additionalParameters = array()) {
		$moduleCode = $this->getModuleCode();

		// Add possible submodule if current module has page tree.
		if ($this->copeWithPageTree() && !isset($additionalParameters[Parameter::SUBMODULE])) {
			$additionalParameters[Parameter::SUBMODULE] = $this->getVidiModuleCode();
		}

		// And don't forget the pid!
		if (GeneralUtility::_GET(Parameter::PID)) {
			$additionalParameters[Parameter::PID] = GeneralUtility::_GET(Parameter::PID);
		}

		$moduleUrl = BackendUtility::getModuleUrl($moduleCode, $additionalParameters);
		return $moduleUrl;
	}

	/**
	 * Return the parameter prefix for a BE module.
	 *
	 * @return string
	 */
	public function getParameterPrefix() {
		return 'tx_vidi_' . strtolower($this->getModuleCode());
	}

	/**
	 * Return a configuration key or the entire module configuration array if not key is given.
	 *
	 * @param string $key
	 * @throws InvalidKeyInArrayException
	 * @return mixed
	 */
	public function getModuleConfiguration($key = '') {

		$vidiModuleCode = $this->getVidiModuleCode();

		// Module code must exist
		if (empty($GLOBALS['TBE_MODULES_EXT']['vidi'][$vidiModuleCode])) {
			$message = sprintf('Invalid or not existing module code "%s"', $vidiModuleCode);
			throw new InvalidKeyInArrayException($message, 1375092053);
		}

		$result = $GLOBALS['TBE_MODULES_EXT']['vidi'][$vidiModuleCode];

		if (!empty($key)) {
			if (isset($result[$key])) {
				$result = $result[$key];
			} else {
				// key must exist
				$message = sprintf('Invalid key configuration "%s"', $key);
				throw new InvalidKeyInArrayException($message, 1375092054);
			}
		}
		return $result;
	}

	/**
	 * @param string $icon
	 * @return $this
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @param string $mainModule
	 * @return $this
	 */
	public function setMainModule($mainModule) {
		$this->mainModule = $mainModule;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMainModule() {
		return $this->mainModule;
	}

	/**
	 * @param string $moduleLanguageFile
	 * @return $this
	 */
	public function setModuleLanguageFile($moduleLanguageFile) {
		$this->moduleLanguageFile = $moduleLanguageFile;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getModuleLanguageFile() {
		return $this->moduleLanguageFile;
	}

	/**
	 * @param string $position
	 * @return $this
	 */
	public function setPosition($position) {
		$this->position = $position;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * @param array $files
	 * @return $this
	 */
	public function addJavaScriptFiles(array $files) {
		foreach ($files as $file) {
			$this->additionalJavaScriptFiles[] = $file;
		}
		return $this;
	}

	/**
	 * @param array $files
	 * @return $this
	 */
	public function addStyleSheetFiles(array $files) {
		foreach ($files as $file) {
			$this->additionalStyleSheetFiles[] = $file;
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		if (empty($this->dataType)) {
			$this->dataType = $this->getModuleConfiguration('dataType');
		}
		return $this->dataType;
	}

	/**
	 * @return array
	 */
	public function getDataTypes() {
		$dataTypes = array();
		foreach ($GLOBALS['TBE_MODULES_EXT']['vidi'] as $module) {
			$dataTypes[] = $module['dataType'];
		}
		return $dataTypes;
	}

	/**
	 * @param string $dataType
	 * @return $this
	 */
	public function setDataType($dataType) {
		$this->dataType = $dataType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultPid() {
		if (empty($this->defaultPid)) {
			$this->defaultPid = $this->getModuleConfiguration('defaultPid');
		}
		return $this->defaultPid;
	}

	/**
	 * @param string $defaultPid
	 * @return $this
	 */
	public function setDefaultPid($defaultPid) {
		$this->defaultPid = $defaultPid;
		return $this;
	}

	/**
	 * @param string $isPageTreeShown
	 * @return $this
	 */
	public function showPageTree($isPageTreeShown) {
		$this->showPageTree = $isPageTreeShown;
		return $this;
	}

	/**
	 * @param string $isShown
	 * @return $this
	 */
	public function isShown($isShown) {
		$this->isShown = $isShown;
		return $this;
	}

	/**
	 * @return $array
	 */
	public function getDocHeaderTopLeftComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setDocHeaderTopLeftComponents(array $components) {
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addDocHeaderTopLeftComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT];
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::LEFT] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function setNavigationTopLeftComponents(array $components) {
		return $this->setDocHeaderTopLeftComponents($components);
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function addNavigationTopLeftComponents(array $components) {
		return $this->addDocHeaderTopLeftComponents($components);
	}

	/**
	 * @return $array
	 */
	public function getDocHeaderTopRightComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setDocHeaderTopRightComponents(array $components) {
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addDocHeaderTopRightComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT];
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::TOP][ModulePosition::RIGHT] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function setNavigationTopRightComponents(array $components) {
		return $this->setDocHeaderTopRightComponents($components);
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function addNavigationTopRightComponents(array $components) {
		return $this->addDocHeaderTopRightComponents($components);
	}

	/**
	 * @return $array
	 */
	public function getDocHeaderBottomLeftComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setDocHeaderBottomLeftComponents(array $components) {
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addDocHeaderBottomLeftComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT];
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::LEFT] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function setNavigationBottomLeftComponents(array $components) {
		return $this->setDocHeaderBottomLeftComponents($components);
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function addNavigationBottomLeftComponents(array $components) {
		return $this->addDocHeaderBottomLeftComponents($components);
	}

	/**
	 * @return $array
	 */
	public function getDocHeaderBottomRightComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setDocHeaderBottomRightComponents(array $components) {
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addDocHeaderBottomRightComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT];
		$this->components[ModulePosition::DOC_HEADER][ModulePosition::BOTTOM][ModulePosition::RIGHT] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function setNavigationBottomRightComponents(array $components) {
		return $this->setDocHeaderBottomRightComponents($components);
	}

	/**
	 * @param array $components
	 * @return $this
	 * @deprecated will be removed in 0.4.0 + 2 version.
	 */
	public function addNavigationBottomRightComponents(array $components) {
		return $this->addDocHeaderBottomRightComponents($components);
	}

	/**
	 * @return $array
	 */
	public function getGridTopComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::GRID][ModulePosition::TOP];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setGridTopComponents(array $components) {
		$this->components[ModulePosition::GRID][ModulePosition::TOP] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addGridTopComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::GRID][ModulePosition::TOP];
		$this->components[ModulePosition::GRID][ModulePosition::TOP] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @return $array
	 */
	public function getGridBottomComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::GRID][ModulePosition::BOTTOM];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setGridBottomComponents(array $components) {
		$this->components[ModulePosition::GRID][ModulePosition::BOTTOM] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addGridBottomComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::GRID][ModulePosition::BOTTOM];
		$this->components[ModulePosition::GRID][ModulePosition::BOTTOM] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @return $array
	 */
	public function getGridButtonsComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::GRID][ModulePosition::BUTTONS];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setGridButtonsComponents(array $components) {
		$this->components[ModulePosition::GRID][ModulePosition::BUTTONS] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addGridButtonsComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::GRID][ModulePosition::BUTTONS];
		$this->components[ModulePosition::GRID][ModulePosition::BUTTONS] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @return $array
	 */
	public function getMenuMassActionComponents() {
		$configuration = $this->getModuleConfiguration();
		return $configuration['components'][ModulePosition::MENU_MASS_ACTION];
	}

	/**
	 * @param array $components
	 * @return $this
	 */
	public function setMenuMassActionComponents(array $components) {
		$this->components[ModulePosition::MENU_MASS_ACTION] = $components;
		return $this;
	}

	/**
	 * @param string|array $components
	 * @return $this
	 */
	public function addMenuMassActionComponents($components) {
		if (is_string($components)) {
			$components = array($components);
		}
		$currentComponents = $this->components[ModulePosition::MENU_MASS_ACTION];
		$this->components[ModulePosition::MENU_MASS_ACTION] = array_merge($currentComponents, $components);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAccess() {
		return $this->access;
	}

	/**
	 * @param string $access
	 * @return $this
	 */
	public function setAccess($access) {
		$this->access = $access;
		return $this;
	}

	/**
	 * @return \string[]
	 */
	public function getAdditionalJavaScriptFiles() {
		if (empty($this->additionalJavaScriptFiles)) {
			$this->additionalJavaScriptFiles = $this->getModuleConfiguration('additionalJavaScriptFiles');
		}
		return $this->additionalJavaScriptFiles;
	}

	/**
	 * @return \string[]
	 */
	public function getAdditionalStyleSheetFiles() {
		if (empty($this->additionalStyleSheetFiles)) {
			$this->additionalStyleSheetFiles = $this->getModuleConfiguration('additionalStyleSheetFiles');
		}
		return $this->additionalStyleSheetFiles;
	}

	/**
	 * @return array
	 */
	public function getComponents() {
		return $this->components;
	}
}
