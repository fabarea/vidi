<?php
namespace TYPO3\CMS\Vidi\Module;
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
	 * @return \TYPO3\CMS\Vidi\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ModuleLoader');
	}
}
