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
 * View helper which return the parameter prefix for a BE module.
 */
class ParameterPrefixViewHelper extends AbstractViewHelper {

	/**
	 * Return the parameter prefix for a BE module.
	 *
	 * @return string the value
	 */
	public function render() {
		$moduleCode = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('M');
		return 'tx_vidi_' . strtolower($moduleCode);
	}
}
