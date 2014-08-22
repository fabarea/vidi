<?php
namespace TYPO3\CMS\Vidi\View\MenuItem;

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

use TYPO3\CMS\Vidi\View\AbstractComponentView;

/**
 * View helper which renders a "divider" menu item to be placed in the grid menu.
 */
class DividerMenuItem extends AbstractComponentView {

	/**
	 * Renders a "divider" menu item to be placed in the grid menu.
	 *
	 * @return string
	 */
	public function render() {
		return '<li class="divider"></li>';
	}
}
