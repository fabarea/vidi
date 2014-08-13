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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Service related to data type (AKA tablename)
 */
class ModuleService implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $storage = array();

	/**
	 * Returns a class instance
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModuleService
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('\TYPO3\CMS\Vidi\Module\ModuleService');
	}

	/**
	 * Fetch all modules to be displayed on the current pid.
	 *
	 * @return array
	 */
	public function getModulesForCurrentPid() {
		$pid = $this->getModuleLoader()->getCurrentPid();
		return $this->getModulesForPid($pid);

	}

	/**
	 * Fetch all modules displayed given a pid.
	 *
	 * @param int $pid
	 * @return array
	 */
	public function getModulesForPid($pid = NULL) {
		if (!isset($this->storage[$pid])) {

			$modules = array();
			foreach ($GLOBALS['TCA'] as $dataType => $configuration) {
				if (TcaService::table($dataType)->isNotHidden()) {
					$clause = 'pid = ' . $pid;
					$clause .= BackendUtility::deleteClause($dataType);
					$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('uid', $dataType, $clause);
					if (!empty($record)) {
						$moduleName = 'Vidi' . GeneralUtility::underscoredToUpperCamelCase($dataType)  . 'M1';
						$title = TcaService::table($dataType)->getTitle();
						$modules[$moduleName] = $title;
					}
				}
			}
			$this->storage[$pid] = $modules;
		}
		return $this->storage[$pid];
	}

	/**
	 * Fetch the first module for the current pid.
	 *
	 * @return string
	 */
	public function getFirstModuleForCurrentPid() {
		$pid = $this->getModuleLoader()->getCurrentPid();
		return $this->getFirstModuleForPid($pid);
	}

	/**
	 * Fetch the module given a pid.
	 *
	 * @param int $pid
	 * @return string
	 */
	public function getFirstModuleForPid($pid) {
		$firstModule = '';
		$modules = $this->getModulesForPid($pid);
		if (!empty($modules)) {
			$firstModule = key($modules);
		}

		return $firstModule;
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
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}
}
