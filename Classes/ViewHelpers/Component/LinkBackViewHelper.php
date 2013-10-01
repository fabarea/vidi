<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Component;
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
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Vidi\Tca\TcaServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper which renders a "back" buttons to be placed in the doc header.
 */
class LinkBackViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Returns the "back" buttons to be placed in the doc header.
	 *
	 * @return string
	 */
	public function render() {

		$result = '';
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('returnUrl')) {
			$result = sprintf('<a href="%s" class="btn-return-top">%s</a>',
				GeneralUtility::_GP('returnUrl'),
				IconUtility::getSpriteIcon('actions-document-close')
			);
		}

		return $result;
	}
}

?>