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

use TYPO3\CMS\Core\Type\Enumeration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Enumeration object for preference name.
 */
class ConfigurablePart extends Enumeration {

	const __default = '';
	const EXCLUDED_FIELDS = 'excluded_fields';
	const MENU_VISIBLE_ITEMS = 'menuVisibleItems';
	const MENU_VISIBLE_ITEMS_DEFAULT = 'menuVisibleItemsDefault';

	/**
	 * @var \Fab\Vidi\Module\ConfigurablePart
	 */
	static protected $instance;

	/**
	 * Get the valid values for this enum.
	 *
	 * @param boolean $include_default
	 * @return array
	 */
	static public function getConstants($include_default = FALSE) {

		// Must be instantiated once to load the values.
		if (is_null(self::$instance)) {
			self::$instance = GeneralUtility::makeInstance('Fab\Vidi\Module\ConfigurablePart');
		}
		return parent::getConstants($include_default);
	}

}