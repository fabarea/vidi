<?php
namespace Fab\Vidi\ViewHelpers;

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

/**
 * View helper which connects the Module Loader object.
 */
class ModulePreferencesViewHelper extends AbstractViewHelper {

	/**
	 * Interface with the Module Loader
	 *
	 * @param string $key
	 * @return string
	 */
	public function render($key) {
		$getter = 'get' . ucfirst($key);

		/** @var \Fab\Vidi\Module\ModulePreferences $modulePreferences */
		$modulePreferences = $this->objectManager->get('Fab\Vidi\Module\ModulePreferences');
		return $modulePreferences->$getter();
	}

}
