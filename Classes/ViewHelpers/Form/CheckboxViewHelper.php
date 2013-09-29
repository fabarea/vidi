<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Form;
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
 * View helper which render a checkbox and mark whether the User belongs to the User Group.
 *
 * @category    ViewHelpers
 * @package     TYPO3
 * @subpackage  media
 * @author      Fabien Udriot <fabien.udriot@typo3.org>
 */
class CheckboxViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render a checkbox and mark whether the User belongs to the User Group.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $content
	 * @param string $relationProperty
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $relatedContent
	 * @return boolean
	 */
	public function render($content, $relationProperty, $relatedContent) {

		/** @var \TYPO3\CMS\Vidi\ViewHelpers\BelongsToViewHelper $belongsToViewHelper */
		$belongsToViewHelper = $this->objectManager->get('TYPO3\CMS\Vidi\ViewHelpers\BelongsToViewHelper');

		$template = '<input type="checkbox" name="arguments[relatedContents][]" value="%s" %s/>';

		return sprintf($template,
			$relatedContent->getUid(),
			$belongsToViewHelper->render($content, $relationProperty, $relatedContent) ? 'checked="checked"' : ''
		);
	}
}

?>