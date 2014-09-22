<?php
namespace TYPO3\CMS\Vidi\Domain\Validator;

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

/**
 * Validate "language"
 */
class LanguageValidator {

	/**
	 * Check whether the $language is valid.
	 *
	 * @param int $language
	 * @throws \Exception
	 * @return void
	 */
	public function validate($language) {

		if (!$this->getLanguageService()->languageExists((int)$language)) {
			throw new \Exception('The language "' . $language . '" does not exist', 1351605542);
		}
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Language\LanguageService
	 */
	protected function getLanguageService() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Language\LanguageService');
	}

}
