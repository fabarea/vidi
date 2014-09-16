<?php
namespace TYPO3\CMS\Vidi\Controller\Backend;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Vidi\Tool\ToolInterface;
use TYPO3\CMS\Vidi\Tool\ToolRegistry;

/**
 * Controller which handles tools related to a Vidi module.
 */
class ToolController extends ActionController {

	/**
	 * @return void
	 */
	public function welcomeAction() {
		$items = array();
		$tools = ToolRegistry::getInstance()->getTools($this->getModuleLoader()->getDataType());

		foreach ($tools as $index => $tool) {

			// Order items by columns / rows
			$position = (int)($index / 3);
			if (!isset($items[$position])) {
				$items[$position] = array();
			}
			$item['title'] = $tool->getTitle();
			$item['description'] = $tool->getDescription();

			$items[$position][] = $item;
		}
		$this->view->assign('orderedItems', $items);
	}

	/**
	 * @param string $tool
	 * @param array $arguments
	 * @return void
	 * @validate $tool TYPO3\CMS\Vidi\Domain\Validator\ToolValidator
	 */
	public function workAction($tool, array $arguments = array()) {
		/** @var ToolInterface $tool */
		$tool = GeneralUtility::makeInstance($tool);
		$workResult = $tool->work($arguments);
		$this->view->assign('result', $workResult);
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
