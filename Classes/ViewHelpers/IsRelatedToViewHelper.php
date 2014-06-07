<?php
namespace TYPO3\CMS\Vidi\ViewHelpers;
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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper for telling whether a Content is related to another Content.
 * e.g a User belongs to a User Group.
 */
class IsRelatedToViewHelper extends AbstractViewHelper {

	/**
	 * Tells whether a Content is related to another content.
	 * The $fieldName corresponds to the relational field name
	 * between the first content object and the second.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $relatedContent
	 * @return boolean
	 */
	public function render($relatedContent) {

		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $content */
		$content = $this->templateVariableContainer->get('content');
		$fieldName = $this->templateVariableContainer->get('fieldName');

		// Build an array of user group uids
		$relatedContentsIdentifiers = array();

		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $contentObject */
		foreach ($content[$fieldName] as $contentObject) {
			$relatedContentsIdentifiers[] = $contentObject->getUid();
		}

		return in_array($relatedContent->getUid(), $relatedContentsIdentifiers);
	}
}
