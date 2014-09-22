<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid\Column;

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
 * Return a possible column header.
 */
class HeaderViewHelper extends AbstractViewHelper {

	/**
	 * Returns a possible column header.
	 *
	 * @param string $name the column Name
	 * @return boolean
	 */
	public function render($name) {
		return TcaService::grid()->getHeader($name);
	}

}
