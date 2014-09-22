<?php
namespace TYPO3\CMS\Vidi\View\Uri;

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

use TYPO3\CMS\Vidi\View\AbstractComponentView;
use TYPO3\CMS\Vidi\Domain\Model\Content;

/**
 * View which renders a "edit" button to be placed in the grid.
 */
class EditUri extends AbstractComponentView {

	/**
	 * Renders a "edit" button to be placed in the grid.
	 *
	 * @param Content $object
	 * @return string
	 */
	public function render(Content $object = NULL) {
		return sprintf('alt_doc.php?returnUrl=%s&edit[%s][%s]=edit',
			rawurlencode($this->getModuleLoader()->getModuleUrl()),
			rawurlencode($object->getDataType()),
			$object->getUid()
		);
	}

}
