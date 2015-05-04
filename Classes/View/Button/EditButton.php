<?php
namespace Fab\Vidi\View\Button;

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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Fab\Vidi\View\AbstractComponentView;
use Fab\Vidi\Domain\Model\Content;

/**
 * View which renders a "edit" button to be placed in the grid.
 */
class EditButton extends AbstractComponentView {

	/**
	 * Renders a "edit" button to be placed in the grid.
	 *
	 * @param Content $object
	 * @return string
	 */
	public function render(Content $object = NULL) {
		return sprintf('<a href="%s" data-uid="%s" class="btn-edit" title="%s">%s</a>',
			$this->getUriRenderer()->render($object),
			$object->getUid(),
			LocalizationUtility::translate('edit', 'vidi'),
			IconUtility::getSpriteIcon('actions-document-open')
		);
	}

	/**
	 * @return \Fab\Vidi\View\Uri\EditUri
	 */
	protected function getUriRenderer() {
		return GeneralUtility::makeInstance('Fab\Vidi\View\Uri\EditUri');
	}

}
