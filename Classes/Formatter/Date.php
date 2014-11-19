<?php
namespace TYPO3\CMS\Vidi\Formatter;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Format a date that will be displayed in the Grid
 */
class Date implements FormatterInterface, SingletonInterface {

	/**
	 * Format a date
	 *
	 * @param int $value
	 * @return string
	 */
	public function format($value) {
		$result = '';
		if ($value > 0) {

			/** @var $viewHelper \TYPO3\CMS\Fluid\ViewHelpers\Format\DateViewHelper */
			$viewHelper = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\ViewHelpers\Format\DateViewHelper');
			$result = $viewHelper->render('@' . $value, $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']);
		}
		return $result;
	}

}
