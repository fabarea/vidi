<?php
namespace TYPO3\CMS\Vidi\Module;

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
 * Enumeration object for positioning components in a Vidi module.
 */
class ModulePosition extends Enumeration {

	const DOC_HEADER = 'doc-header';

	const TOP = 'top';

	const BOTTOM = 'bottom';

	const LEFT = 'left';

	const RIGHT = 'right';

	const GRID = 'grid';

	const BUTTONS = 'buttons';

	const MENU_SELECTED_ROWS = 'selected-rows';

	const MENU_ALL_ROWS = 'all-rows';
}