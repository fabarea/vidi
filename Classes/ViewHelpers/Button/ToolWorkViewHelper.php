<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Button;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which renders a button "work" for a Tool.
 */
class ToolWorkViewHelper extends AbstractViewHelper {

	/**
	 * Renders a button for "work" for a Tool.
	 *
	 * @param string $tool
	 * @param string $label
	 * @param array $arguments
	 * @return string
	 */
	public function render($tool, $label, $arguments = array()) {

		$parameterPrefix = $this->getModuleLoader()->getParameterPrefix();

		// Compute the additional parameters.
		$additionalParameters = array(
			$parameterPrefix => array(
				'controller' => 'Tool',
				'action' => 'work',
				'tool' => $tool,
			),
		);

		// Add possible additional arguments.
		if (!empty($arguments)) {
			$additionalParameters[$parameterPrefix]['arguments'] = $arguments;
		}

		$result = sprintf('<a href="%s&returnUrl=%s" class="btn">%s</a>',
			$this->getModuleLoader()->getModuleUrl($additionalParameters),
			urlencode($GLOBALS['_SERVER']['REQUEST_URI']),
			$label
		);
		return $result;
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}
}
