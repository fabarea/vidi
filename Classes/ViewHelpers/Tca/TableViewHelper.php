<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Tca;

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
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * View helper which wraps the TCA Table service.
 */
class TableViewHelper extends AbstractViewHelper {

	/**
	 * Returns a value from the TCA Table service according to a key.
	 *
	 * @param string $key
	 * @param string $dataType
	 * @return string
	 */
	public function render($key, $dataType = '') {
		$result = TcaService::table($dataType)->getTca();

		// Explode segment and loop around.
		$keys = explode('|', $key);
		foreach ($keys as $key) {
			if (!empty($result[$key])) {
				$result = $result[$key];
			} else {
				// not found value
				$result = FALSE;
				break;
			}
		}

		return $result;
	}

}
