<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Link;

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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which renders a "back" buttons to be placed in the doc header.
 */
class BackViewHelper extends AbstractViewHelper {

	/**
	 * Returns the "back" buttons to be placed in the doc header.
	 *
	 * @return string
	 */
	public function render() {

		$result = '';
		if (GeneralUtility::_GET('returnUrl')) {
			$result = sprintf('<a href="%s" class="btn-return-top">%s</a>',
				GeneralUtility::_GP('returnUrl'),
				IconUtility::getSpriteIcon('actions-document-close')
			);
		}

		return $result;
	}
}
