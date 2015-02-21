<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Selection;

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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which returns the options for the visibility field of a Selection.
 */
class VisibilityOptionsViewHelper extends AbstractViewHelper {

	/**
	 * Returns the options for the visibility field of a Selection.
	 *
	 * @return array
	 */
	public function render() {
		$options = array(
			LocalizationUtility::translate('LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:visibility.everyone', 'vidi'),
			LocalizationUtility::translate('LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:visibility.private', 'vidi'),
		);

		if ($this->getBackendUser()->isAdmin()) {
			$options[] = LocalizationUtility::translate('LLL:EXT:vidi/Resources/Private/Language/tx_vidi_domain_model_selection.xlf:visibility.admin_only', 'vidi');
		}
		return $options;
	}


	/**
	 * Returns an instance of the current Backend User.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}


}
