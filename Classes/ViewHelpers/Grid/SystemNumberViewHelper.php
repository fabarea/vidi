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
 * View helper for rendering the position number of an content object.
 * @todo Not working currently because $index is not given. Implement me if needed.
 */
class SystemNumberViewHelper extends AbstractViewHelper {

	/**
	 * Renders the position number of an content object.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @param  int $offset
	 * @param  int $index
	 * @return string
	 */
	public function render(\TYPO3\CMS\Vidi\Domain\Model\Content $object, $offset, $index) {
		return $index + 1 + $offset;
	}
}
