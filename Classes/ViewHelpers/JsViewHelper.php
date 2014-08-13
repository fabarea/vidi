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
 * View helper which allows you to include a JS File.
 */
class JsViewHelper extends AbstractViewHelper {

	/**
	 * Compute a JS tag and render it
	 *
	 * @param string $name the file to include
	 * @param string $extKey the extension, where the file is located
	 * @param string $pathInsideExt the path to the file relative to the ext-folder
	 * @return string the link
	 */
	public function render($name = NULL, $extKey = NULL, $pathInsideExt = 'Resources/Public/JavaScript/') {

		if ($extKey === NULL) {
			$extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();
		}

		if (TYPO3_MODE === 'FE') {
			$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey);
			$extRelPath = substr($extPath, strlen(PATH_site));
		} else {
			$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($extKey);
		}

		return sprintf('<script src="%s%s%s"></script>', $extRelPath, $pathInsideExt, $name);
	}

}
