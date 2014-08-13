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

/**
 * Format a value to be displayed in a Grid
 */
interface FormatterInterface {

	/**
	 * Format a date
	 *
	 * @param string $value
	 * @return string
	 */
	public function format($value);

}
