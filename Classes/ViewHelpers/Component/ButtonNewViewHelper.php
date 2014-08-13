<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Component;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper which renders a "new" button to be placed in the doc header.
 */
class ButtonNewViewHelper extends AbstractViewHelper {

	/**
	 * Renders a "new" button to be placed in the doc header.
	 *
	 * @return string
	 */
	public function render() {

		/** @var \TYPO3\CMS\Vidi\ViewHelpers\Uri\CreateViewHelper $uriCreateViewHelper */
		$uriCreateViewHelper = $this->objectManager->get('TYPO3\CMS\Vidi\ViewHelpers\Uri\CreateViewHelper');
		$uriCreateViewHelper->initialize();

		return sprintf('<a href="%s" class="btn-new-top" title="%s">%s</a>',
			$uriCreateViewHelper->render(),
			LocalizationUtility::translate('new', 'vidi'),
			IconUtility::getSpriteIcon('actions-document-new')
		);
	}
}
