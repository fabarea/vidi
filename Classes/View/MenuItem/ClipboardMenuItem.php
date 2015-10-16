<?php
namespace Fab\Vidi\View\MenuItem;

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

use Fab\Media\Module\MediaModule;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Fab\Vidi\View\AbstractComponentView;

/**
 * View which renders a "mass delete" menu item to be placed in the grid menu.
 */
class ClipboardMenuItem extends AbstractComponentView {

	/**
	 * Renders a "mass delete" menu item to be placed in the grid menu.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';
		if ($this->getMediaModule()->hasFolderTree()) {
			$output = sprintf('<li><a href="%s" class="clipboard-save" >%s %s</a>',
				$this->getSaveInClipboardUri(),
				IconUtility::getSpriteIcon('extensions-vidi-clipboard'),
				LocalizationUtility::translate('clipboard.save', 'vidi')
			);
		}
		return $output;
	}

	/**
	 * @return string
	 */
	protected function getSaveInClipboardUri() {
		$additionalParameters = array(
			$this->getModuleLoader()->getParameterPrefix() => array(
				'controller' => 'Clipboard',
				'action' => 'save',
				'format' => 'json',
			),
		);
		return $this->getModuleLoader()->getModuleUrl($additionalParameters);
	}

	/**
	 * @return MediaModule
	 */
	protected function getMediaModule() {
		return GeneralUtility::makeInstance('Fab\Media\Module\MediaModule');
	}
}
