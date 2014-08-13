<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid;

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
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Module\ModuleLoader;

/**
 * View helper for rendering buttons in the grids according to a Content object.
 */
class SystemButtonsViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Rendering buttons in the grids given a Content object.
	 *
	 * @param Content $object
	 * @return string
	 */
	public function render(Content $object) {

		/** @var ModuleLoader $moduleLoader */
		$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\Module\ModuleLoader');
		$components = $moduleLoader->getGridButtonsComponents();

		$result = '';
		foreach ($components as $component) {
			$viewHelper = $this->objectManager->get($component);
			$result .= $viewHelper->render($object);
		}

		return $result;
	}

}
