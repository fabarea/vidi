<?php
namespace TYPO3\CMS\Vidi\Language;

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

use TYPO3\CMS\Core\Type\Enumeration;

/**
 * Enumeration object for localization status.
 */
class LocalizationStatus extends Enumeration {

	const LOCALIZED = 'localized';
	const NOT_YET_LOCALIZED = 'notYetLocalized';
	const EMPTY_VALUE = 'emptyValue';

}
