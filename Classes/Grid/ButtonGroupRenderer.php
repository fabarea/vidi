<?php
namespace TYPO3\CMS\Vidi\Grid;

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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for rendering the "Button Group" in the Grid, e.g. edit, delete, etc..
 */
class ButtonGroupRenderer extends GridRendererAbstract {

	/**
	 * Render the "Button Group" in the Grid, e.g. edit, delete, etc..
	 *
	 * @return string
	 */
	public function render() {

		$components = $this->getModuleLoader()->getGridButtonsComponents();

		$result = '';
		foreach ($components as $component) {

			/** @var  $view */
			$view = GeneralUtility::makeInstance($component);
			$result .= $view->render($this->getObject());
		}

		return $result;
	}

}
