<?php
namespace TYPO3\CMS\Vidi\View\Button;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\View\AbstractComponentView;

/**
 * View which renders a "delete" button to be placed in the grid.
 */
class DeleteButton extends AbstractComponentView {

	/**
	 * Renders a "delete" button to be placed in the grid.
	 *
	 * @param Content $object
	 * @return string
	 */
	public function render(Content $object = NULL) {
		return sprintf('<a href="%s" data-uid="%s" class="btn-delete" >%s</a>',
			$this->getDeleteUri($object),
			$object->getUid(),
			IconUtility::getSpriteIcon('actions-edit-delete')
		);
	}

	/**
	 * @param Content $object
	 * @return string
	 */
	protected function getDeleteUri(Content $object) {
		$additionalParameters = array(
			$this->getModuleLoader()->getParameterPrefix() => array(
				'controller' => 'Content',
				'action' => 'delete',
				'format' => 'json',
				'matches' => array(
					'uid' => $object->getUid(),
				),
			),
		);
		return $this->getModuleLoader()->getModuleUrl($additionalParameters);
	}

}
