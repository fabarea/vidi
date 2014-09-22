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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which returns an array of available languages.
 */
class LanguagesViewHelper extends AbstractViewHelper {

	/**
	 * Returns an array of available languages.
	 *
	 * @return array
	 */
	public function render() {
		$languages[0] = $this->getLanguageService()->getDefaultFlag();

		foreach ($this->getLanguageService()->getLanguages() as $language) {

			$languages[$language['uid']] = $language['flag'];
		}

		return $languages;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Language\LanguageService
	 */
	protected function getLanguageService() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Language\LanguageService');
	}
}
