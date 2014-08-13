<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Render;

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
use TYPO3\CMS\Vidi\Module\ModuleLoader;

/**
 * View helper for rendering components
 */
class ComponentsViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Renders the position number of an content object.
	 *
	 * @param  string $part
	 * @return string
	 */
	public function render($part) {

		/** @var ModuleLoader $moduleLoader */
		$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\Module\ModuleLoader');

		$getComponents = 'get' . ucfirst($part) . 'Components';
		$components = $moduleLoader->$getComponents();

		$result = '';
		foreach ($components as $component) {
			$viewHelper = $this->objectManager->get($component);

			// Get possible arguments but remove first one.
			$arguments = func_get_args();
			array_shift($arguments);
			$result .= call_user_func_array(array($viewHelper, 'render'), $arguments);
		}

		return $result;
	}
}
