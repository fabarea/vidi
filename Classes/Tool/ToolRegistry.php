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
	 * Tell whether the given data type has any tools registered.
	 *
	 * @param string $dataType
	 * @return bool
	 */
	public function hasAnyTools($dataType) {

		$hasAnyTools = FALSE;
		$tools = $this->getTools($dataType);

		foreach ($tools as $tool) {
			if ($tool->isShown()) {
				$hasAnyTools = TRUE;
				break;
			}
		}

		return $hasAnyTools;
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
					$tool = GeneralUtility::makeInstance($toolName);
					$tools[] = $tool;
				}
			}
		}
		return $tools;
	}

}
