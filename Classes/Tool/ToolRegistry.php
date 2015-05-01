<?php
namespace TYPO3\CMS\Vidi\Tool;

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
use Closure;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Relation Analyser for a Vidi module.
 */
class ToolRegistry implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $tools = array();

	/**
	 * @var array
	 */
	protected $overriddenPermissions = array();

	/**
	 * Returns a class instance.
	 *
	 * @return \TYPO3\CMS\Vidi\Tool\ToolRegistry
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Tool\ToolRegistry');
	}

	/**
	 * Register a tool for a data type.
	 *
	 * @param string $dataType corresponds to the table name or can be "*" for all data types.
	 * @param string $toolName class name which must implement "ToolInterface".
	 * @return $this
	 */
	public function register($dataType, $toolName) {
		if (!isset($this->tools[$dataType])) {
			$this->tools[$dataType] = array();
		}

		$this->tools[$dataType][] = $toolName;
		return $this;
	}

	/**
	 * Override permissions for a tool by passing a Closure that will be evaluated when checking permissions.
	 *
	 * @param string $dataType corresponds to the table name or can be "*" for all data types.
	 * @param string $toolName class name which must implement "ToolInterface".
	 * @param $permission
	 * @return $this
	 */
	public function overridePermission($dataType, $toolName, Closure $permission) {
		if(empty($this->overriddenPermissions[$dataType])) {
			$this->overriddenPermissions[$dataType] = array();
		}

		$this->overriddenPermissions[$dataType][$toolName] = $permission;
		return $this;
	}

	/**
	 * Un-Register a tool for a given data type.
	 *
	 * @param string $dataType corresponds to the table name or can be "*" for all data types.
	 * @param string $toolName class name which must implement "ToolInterface".
	 * @return $this
	 */
	public function unRegister($dataType, $toolName) {
		if ($this->hasTools($dataType, $toolName)) {

			$toolPosition = array_search($toolName, $this->tools['*']);
			if ($toolPosition !== FALSE) {
				unset($this->tools['*'][$toolPosition]);
			}

			$toolPosition = array_search($toolName, $this->tools[$dataType]);
			if ($toolPosition !== FALSE) {
				unset($this->tools[$dataType][$toolPosition]);
			}
		}

		return $this;
	}

	/**
	 * Tell whether the given data type has any tools registered.
	 *
	 * @param string $dataType
	 * @return bool
	 */
	public function hasAnyTools($dataType) {
		$tools = $this->getTools($dataType);
		return !empty($tools);
	}

	/**
	 * Tell whether the given data type has this $tool.
	 *
	 * @param string $dataType
	 * @param string $tool
	 * @return bool
	 */
	public function hasTools($dataType, $tool) {
		return in_array($tool, $this->tools['*']) || in_array($tool, $this->tools[$dataType]);
	}

	/**
	 * Tell whether the given tool is allowed for this data type.
	 *
	 * @param string $dataType
	 * @param string $toolName
	 * @return bool
	 */
	public function isAllowed($dataType, $toolName) {
		$isAllowed = FALSE;

		if ($this->hasTools($dataType, $toolName)) {

			$permission = $this->getOverriddenPermission($dataType, $toolName);
			if (!is_null($permission)) {
				$isAllowed = $permission();
			} else {
				/** @var ToolInterface $toolName */
				$toolName = GeneralUtility::makeInstance($toolName);
				$isAllowed = $toolName->isShown();
			}

		}
		return $isAllowed;
	}

	/**
	 * Get Registered tools.
	 *
	 * @param string $dataType
	 * @return ToolInterface[]
	 */
	public function getTools($dataType) {
		$tools = array();

		foreach (array($dataType, '*') as $toolSource) {

			if (isset($this->tools[$toolSource])) {
				$toolNames = $this->tools[$toolSource];

				foreach ($toolNames as $toolName) {

					/** @var ToolInterface $tool */
					if ($this->isAllowed($dataType, $toolName)) {
						$tools[] = GeneralUtility::makeInstance($toolName);
					}
				}
			}
		}
		return $tools;
	}

	/**
	 * Get the proper permission for a tool.
	 *
	 * @param string $dataType corresponds to the table name or can be "*" for all data types.
	 * @param string $toolName class name which must implement "ToolInterface".
	 * @return NULL|Closure
	 */
	protected function getOverriddenPermission($dataType, $toolName) {
		$permission = NULL;
		if(isset($this->overriddenPermissions[$dataType][$toolName])) {
			$permission = $this->overriddenPermissions[$dataType][$toolName];
		} elseif (isset($this->overriddenPermissions['*'][$toolName])) {
			$permission = $this->overriddenPermissions['*'][$toolName];
		}
		return $permission;
	}

}
