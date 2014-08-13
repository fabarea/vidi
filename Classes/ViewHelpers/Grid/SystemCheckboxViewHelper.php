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
 * View helper for rendering a checkbox.
 */
class SystemCheckboxViewHelper extends AbstractViewHelper {

	/**
	 * Returns a checkbox for the grids.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param  int $offset
	 * @return string
	 */
	public function render(\TYPO3\CMS\Vidi\Domain\Model\Content $object, $offset) {
		return sprintf('<input type="checkbox" class="checkbox-row" data-index="%s" data-uid="%s"/>',
			$offset,
			$object->getUid()
		);
	}
}
