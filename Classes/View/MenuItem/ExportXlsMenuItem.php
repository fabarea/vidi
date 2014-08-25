<?php
namespace TYPO3\CMS\Vidi\View\MenuItem;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Vidi\View\AbstractComponentView;

/**
 * View which renders a "xls export" item to be placed in the menu.
 */
class ExportXlsMenuItem extends AbstractComponentView {

	/**
	 * Renders a "xls export" item to be placed in the menu.
	 * Only the admin is allowed to export for now as security is not handled.
	 *
	 * @return string
	 */
	public function render() {
		$result = '';
		if ($this->getBackendUser()->isAdmin()) {
			$result = sprintf('<li><a href="#" class="export-xls" data-format="xls">%s %s</a></li>',
				IconUtility::getSpriteIcon('mimetypes-excel'),
				LocalizationUtility::translate('export-xls', 'vidi')
			);
		}
		return $result;
	}

}
