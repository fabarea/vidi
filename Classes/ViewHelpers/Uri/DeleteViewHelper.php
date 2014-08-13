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
use TYPO3\CMS\Vidi\Domain\Model\Content;

/**
 * Render a delete URI given an object.
 */
class DeleteViewHelper extends AbstractViewHelper {

	/**
	 * @var \TYPO3\CMS\Vidi\Module\ModuleLoader
	 * @inject
	 */
	protected $moduleLoader;

	/**
	 * Render a delete URI given an object.
	 *
	 * @param Content $object
	 * @return string
	 */
	public function render(Content $object) {

		$parameterPrefix = $this->moduleLoader->getParameterPrefix();
		$parameterPrefixEncoded = rawurlencode($parameterPrefix);

		return sprintf('%s&%s[matches][uid]=%s&%s[format]=json&%s[action]=delete&%s[controller]=Content',
			BackendUtility::getModuleUrl($this->moduleLoader->getModuleCode()),
			$parameterPrefixEncoded,
			$object->getUid(),
			$parameterPrefixEncoded,
			$parameterPrefixEncoded,
			$parameterPrefixEncoded
		);
	}
}
