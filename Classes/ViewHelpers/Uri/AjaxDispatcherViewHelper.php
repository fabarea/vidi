<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Uri;
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper which renders an URI for the Ajax dispatcher
 *
 * @deprecated use the BE module hiding technique introduced in 6.2.
 */
class AjaxDispatcherViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders an URI for the Ajax dispatcher
	 *
	 * @param string $extensionName
	 * @param string $controllerName
	 * @param string $actionName
	 * @return string
	 */
	public function render($extensionName, $controllerName, $actionName) {
		return sprintf('/typo3/ajax.php?ajaxID=vidiAjaxDispatcher&extensionName=%s&pluginName=Pi1&controllerName=%s&actionName=%s&returnUrl=%s',
			$extensionName,
			$controllerName,
			$actionName,
			urlencode($GLOBALS['_SERVER']['REQUEST_URI'])
		);
	}
}
