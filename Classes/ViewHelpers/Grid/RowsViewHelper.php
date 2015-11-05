<?php
namespace Fab\Vidi\ViewHelpers\Grid;

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

use Fab\Vidi\View\ViewComponentInterface;
use Fab\Vidi\Domain\Model\Content;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;


/**
 * View helper for rendering multiple rows.
 */
class RowsViewHelper extends AbstractViewHelper {

	/**
	 * @var ViewComponentInterface
	 */
	protected $view;

	/**
	 * Returns rows of content as array.
	 *
	 * @param array $objects
	 * @param array $columns
	 * @return string
	 */
	public function render(array $objects = array(), array $columns = array()) {
		$rows = array();

		foreach ($objects as $index => $object) {
			$rows[] = $this->getRowView($object, $columns)->render($object, $index);
		}

		return $rows;
	}

	/**
	 * @param Content $object
	 * @param array $columns
	 * @return ViewComponentInterface
	 */
	public function getRowView(Content $object, $columns) {
		if (is_null($this->view)) {
			// Default class name.
			$viewClassName = '\\Fab\\Vidi\\View\\Grid\\Row';

			if (!empty($GLOBALS['TCA'][$object->getDataType()]['vidi']['classes'][$viewClassName])) {
				$viewClassName = $GLOBALS['TCA'][$object->getDataType()]['vidi']['classes'][$viewClassName];
			}

			$this->view = GeneralUtility::makeInstance($viewClassName, $columns);
		}

		return $this->view;
	}
}
