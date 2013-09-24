<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid\Row;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * View helper for rendering buttons in the grids.
 */
class ButtonsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Rendering buttons in the grids for an object.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $object
	 * @return string
	 */
	public function render(\TYPO3\CMS\Vidi\Domain\Model\Content $object) {

		/** @var \TYPO3\CMS\Vidi\ViewHelpers\Uri\EditViewHelper $uriEditViewHelper */
		$uriEditViewHelper = $this->objectManager->get('TYPO3\CMS\Vidi\ViewHelpers\Uri\EditViewHelper');
		$results[] = sprintf('<a href="%s" data-uid="%s" class="btn-edit">%s</a>',
			$uriEditViewHelper->render($object),
			$object->getUid(),
			\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-open')
		);

		/** @var \TYPO3\CMS\Vidi\ViewHelpers\Uri\DeleteViewHelper $uriDeleteViewHelper */
		$uriDeleteViewHelper = $this->objectManager->get('TYPO3\CMS\Vidi\ViewHelpers\Uri\DeleteViewHelper');

		$results[] = sprintf('<a href="%s" data-uid="%s" class="btn-delete" >%s</a>',
			$uriDeleteViewHelper->render($object),
			$object->getUid(),
			\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-edit-delete')
		);

		return implode(' ', $results);
	}
}

?>