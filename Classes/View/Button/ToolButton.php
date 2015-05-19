<?php
namespace Fab\Vidi\View\Button;

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

use Fab\Vidi\Tool\ToolRegistry;
use Fab\Vidi\View\AbstractComponentView;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * View helper which renders a dropdown menu for storage.
 */
class ToolButton extends AbstractComponentView {

	/**
	 * Renders a dropdown menu for storage.
	 *
	 * @return string
	 */
	public function render() {
		$result = '';

		// Compute the additional parameters.
		$additionalParameters = array(
			$this->getModuleLoader()->getParameterPrefix() => array('controller' => 'Tool', 'action' => 'welcome'),
		);

		// Get current data type and tell whether there are registered tools.
		$dataType = $this->getModuleLoader()->getDataType();

		if (ToolRegistry::getInstance()->hasAnyTools($dataType)) {
			$result = sprintf(
				'<div class="pull-right"><a href="%s&returnUrl=%s" class="btn btn-mini btn-doc-header" title="%s"><span class="icon-cog"></span></a></div>',
				$this->getModuleLoader()->getModuleUrl($additionalParameters),
				urlencode($this->getModuleLoader()->getModuleUrl()),
				LocalizationUtility::translate('open_tools', 'vidi')
			);
		}
		return $result;
	}

}
