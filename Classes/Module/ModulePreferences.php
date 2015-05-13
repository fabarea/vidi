<?php
namespace Fab\Vidi\Module;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class used to retrieve module preferences.
 */
class ModulePreferences implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $preferences;

	/**
	 * @var string
	 */
	protected $tableName = 'tx_vidi_preference';

	/**
	 * @param string $key
	 * @param string $dataType
	 * @return mixed
	 */
	public function get($key, $dataType = '') {

		if (empty($dataType)) {
			$dataType = $this->getModuleLoader()->getDataType();
		}

		if (!$this->isLoaded($dataType)) {
			$this->load($dataType);
		}

		$value = empty($this->preferences[$dataType][$key]) ? NULL : $this->preferences[$dataType][$key];
		return $value;
	}

	/**
	 * Tell whether the module is loaded
	 *
	 * @param string $dataType
	 * @return bool
	 */
	public function isLoaded($dataType) {
		return !empty($this->preferences[$dataType]);
	}

	/**
	 * @param string $dataType
	 * @return array
	 */
	public function getAll($dataType = '') {

		if (empty($dataType)) {
			$dataType = $this->getModuleLoader()->getDataType();
		}
		$this->load($dataType);
		return $this->preferences[$dataType];
	}

	/**
	 * Tell whether the module is loaded
	 *
	 * @param string $dataType
	 * @return bool
	 */
	public function getSignature($dataType = '') {
		$preferences = $this->getAll($dataType);
		return md5(serialize($preferences));
	}

	/**
	 * Load preferences
	 *
	 * @param string $dataType
	 * @return void
	 */
	public function load($dataType) {

		// Fetch preferences from different sources and overlay them
		$databasePreferences = $this->fetchPreferencesFromDatabase($dataType);
		$generalPreferences = $this->fetchGlobalPreferencesFromTypoScript();
		$specificPreferences = $this->fetchExtraPreferencesFromTypoScript($dataType);

		$preferences = array_merge($generalPreferences, $specificPreferences, $databasePreferences);
		$this->preferences[$dataType] = $preferences;
	}

	/**
	 * Save preferences
	 *
	 * @param array $preferences
	 * @return void
	 */
	public function save($preferences) {
		$allowedPreferences = ConfigurablePart::getConstants();

		$dataType = $this->getModuleLoader()->getDataType();
		$clause = sprintf('data_type = "%s"', $dataType);
		$this->getDatabaseConnection()->exec_DELETEquery($this->tableName, $clause);

		$sanitizedPreferences = array();
		foreach ($preferences as $key => $value) {
			if (in_array($key, $allowedPreferences)) {
				$sanitizedPreferences[$key] = $value;
			}
		}

		$values = array(
			'data_type' => $dataType,
			'preferences' => serialize($sanitizedPreferences),
		);
		$this->getDatabaseConnection()->exec_INSERTquery($this->tableName, $values);
	}

	/**
	 * @param $dataType
	 * @return array
	 */
	public function fetchPreferencesFromDatabase($dataType) {
		$preferences = array();

		$clause = sprintf('data_type = "%s"', $dataType);
		$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('*', $this->tableName, $clause);

		if (!empty($record)) {
			$preferences = unserialize($record['preferences']);
		}

		return $preferences;
	}

	/**
	 * Returns the module settings.
	 *
	 * @return array
	 */
	protected function fetchGlobalPreferencesFromTypoScript() {
		$settings = $this->getSettings();

		$allowedPreferences = ConfigurablePart::getConstants();
		$preferences = array();
		foreach ($settings as $key => $value) {
			if (in_array($key, $allowedPreferences)) {
				$preferences[$key] = $value;
			}
		}

		return $preferences;
	}

	/**
	 * Returns the module settings.
	 *
	 * @param string $dataType
	 * @return array
	 */
	protected function fetchExtraPreferencesFromTypoScript($dataType) {
		$generalSettings = $this->getSettings();

		$preferences = array();
		if (isset($generalSettings[$dataType . '.'])) {
			$settings = $generalSettings[$dataType . '.'];

			$allowedPreferences = ConfigurablePart::getConstants();
			foreach ($settings as $key => $value) {
				if (in_array($key, $allowedPreferences)) {
					$preferences[$key] = $value;
				}
			}
		}

		return $preferences;
	}

	/**
	 * Returns the module settings.
	 *
	 * @return array
	 */
	protected function getSettings() {
		/** @var \TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager $backendConfigurationManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$backendConfigurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager');
		$configuration = $backendConfigurationManager->getTypoScriptSetup();
		return $configuration['module.']['tx_vidi.']['settings.'];
	}

	/**
	 * Returns a pointer to the database.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \Fab\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('Fab\Vidi\Module\ModuleLoader');
	}

}
