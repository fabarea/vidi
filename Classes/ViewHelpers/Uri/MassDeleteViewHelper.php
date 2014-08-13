<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Uri;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Render a mass delete URI.
 */
class MassDeleteViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Vidi\Module\ModuleLoader
	 * @inject
	 */
	protected $moduleLoader;

	/**
	 * Render a mass delete URI.
	 *
	 * @return string
	 */
	public function render() {

		$parameterPrefix = $this->moduleLoader->getParameterPrefix();
		$parameterPrefixEncoded = rawurlencode($parameterPrefix);

		return sprintf('%s&%s[format]=json&%s[action]=delete&%s[controller]=Content',
			BackendUtility::getModuleUrl($this->moduleLoader->getModuleCode()),
			$parameterPrefixEncoded,
			$parameterPrefixEncoded,
			$parameterPrefixEncoded
		);
	}
}
