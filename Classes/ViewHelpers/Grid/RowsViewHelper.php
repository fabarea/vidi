<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for rendering multiple rows.
 */
class RowsViewHelper extends AbstractViewHelper {

	/**
	 * Returns rows of content as array.
	 *
	 * @param array $objects
	 * @param array $columns
	 * @return string
	 */
	public function render(array $objects = array(), array $columns = array()) {
		$rows = array();

		/** @var RowViewHelper $rowViewHelper */
		$rowViewHelper = $this->objectManager->get('TYPO3\CMS\Vidi\ViewHelpers\Grid\RowViewHelper', $columns);
		foreach ($objects as $index => $object) {
			$rows[] = $rowViewHelper->render($object, $index);
		}

		return $rows;
	}
}
