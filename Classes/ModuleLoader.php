<?php
namespace TYPO3\CMS\Vidi;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

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
	 * @var string
	 */
	protected $access = 'user,group';

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
	 * @param string $dataType
	 */
	public function __construct($dataType = '') {
		$this->dataType = $dataType;
	}

	/**
	 * Register the module
	 *
	 * @return void
	 */
	public function register() {
		$subModuleName = $this->dataType . '_' . $this->moduleKey;
		$moduleCode = sprintf('%s_Vidi%s',
			$this->mainModule,
			\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($subModuleName)
		);

		$GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode] = array();
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode]['dataType'] = $this->dataType;
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode]['defaultPid'] = $this->defaultPid;
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode]['additionalJavaScriptFiles'] = $this->additionalJavaScriptFiles;
		$GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode]['additionalStyleSheetFiles'] = $this->additionalStyleSheetFiles;

		// @todo improve for loading main module after 6.2 http://forge.typo3.org/issues/49643
		ExtensionUtility::registerModule(
			'vidi',
			$this->mainModule,
			$subModuleName,
			$this->position,
			array(
				'Content' => 'list, listRow, listFacetValues, delete, massDelete, update',
			),
			array(
				'access' => $this->access, // @todo property
				'icon' => $this->icon,
				'labels' => $this->moduleLanguageFile,
			)
		);
	}

	/**
	 * Return a configuraiton key or the entire module configuration array if not key is given.
	 *
	 * @param string $key
	 * @throws Exception\InvalidKeyInArrayException
	 * @return mixed
	 */
	public function getModuleConfiguration($key = '') {
		$moduleCode = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('M');

		// Module code must exist
		if (empty($GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode])) {
			$message = sprintf('Invalid or not existing module code "%s"', $moduleCode);
			throw new \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException($message, 1375092053);
		}

		$result = $GLOBALS['TBE_MODULES_EXT']['vidi'][$moduleCode];

		if (!empty($key)) {
			if (isset($result[$key])) {
				$result = $result[$key];
			} else {
				// key must exist
				$message = sprintf('Invalid key configuration "%s"', $key);
				throw new \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException($message, 1375092054);
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
		if (empty($this->addStyleSheetFiles)) {
			$this->addStyleSheetFiles = $this->getModuleConfiguration('additionalStyleSheetFiles');
		}
		return $this->addStyleSheetFiles;
	}
}
