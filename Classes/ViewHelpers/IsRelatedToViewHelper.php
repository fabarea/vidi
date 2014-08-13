<?php
namespace TYPO3\CMS\Vidi\ViewHelpers;

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

		$isChecked = FALSE;

		// Only computes whether the object is checked if one row is beeing edited.
		$numberOfObjects = $this->templateVariableContainer->get('numberOfObjects');
		if ($numberOfObjects === 1) {

			/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $content */
			$content = $this->templateVariableContainer->get('content');
			$fieldName = $this->templateVariableContainer->get('fieldName');

			// Build an array of user group uids
			$relatedContentsIdentifiers = array();

			/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $contentObject */
			foreach ($content[$fieldName] as $contentObject) {
				$relatedContentsIdentifiers[] = $contentObject->getUid();
			}

			$isChecked = in_array($relatedContent->getUid(), $relatedContentsIdentifiers);
		}

		return $isChecked;
	}
}
