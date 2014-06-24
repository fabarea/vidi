<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Render;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
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
