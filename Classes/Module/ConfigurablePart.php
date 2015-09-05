<?php
namespace Fab\Vidi\Module;

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
 * Enumeration object for preference name.
 */
class ConfigurablePart {

	const __default = '';
	const EXCLUDED_FIELDS = 'excluded_fields';
	const MENU_VISIBLE_ITEMS = 'menuVisibleItems';
	const MENU_VISIBLE_ITEMS_DEFAULT = 'menuVisibleItemsDefault';

	/**
	 * Get the valid values for this enum.
	 *
	 * @param boolean $include_default
	 * @return array
	 */
	static public function getParts($include_default = FALSE) {

		return array(
			'EXCLUDED_FIELDS' => self::EXCLUDED_FIELDS,
			'MENU_VISIBLE_ITEMS' => self::MENU_VISIBLE_ITEMS,
			'MENU_VISIBLE_ITEMS_DEFAULT' => self::MENU_VISIBLE_ITEMS_DEFAULT,
		);
	}

}