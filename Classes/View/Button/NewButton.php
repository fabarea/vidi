<?php
namespace TYPO3\CMS\Vidi\View\Button;

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
use TYPO3\CMS\Vidi\Module\Parameter;
use TYPO3\CMS\Vidi\Tca\TcaService;
use TYPO3\CMS\Vidi\View\AbstractComponentView;

/**
 * View which renders a "new" button to be placed in the doc header.
 */
class NewButton extends AbstractComponentView {

	/**
	 * Renders a "new" button to be placed in the doc header.
	 *
	 * @return string
	 */
	public function render() {

		// General New button
		if ($this->getModuleLoader()->copeWithPageTree()) {

			// Wizard "new", typo3/db_new.php
			$output = sprintf('<a href="%s" title="%s" class="btn-new-top">%s</a>',
				$this->getUriWizardNew(),
				$this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:newRecordGeneral'),
				IconUtility::getSpriteIcon('actions-document-new')
			);

			// Add an icon right to the wizard "new".
			$currentDataType = $this->getModuleLoader()->getDataType();
			$spriteForCurrentDataType = IconUtility::mapRecordTypeToSpriteIconName($currentDataType, array());

			$output .= sprintf(' <a href="%s" title="%s" class="btn-new-top">%s</a>',
				$this->getNewUri(),
				$this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:newRecordGeneral'),
				IconUtility::getSpriteIcon($spriteForCurrentDataType) // temporary code. Find a better solution GUI-wise. Perhaps a dropdown menu with multiple "add" variants.
			);

		} else {

			// New button only for the current data type.
			$output = sprintf('<a href="%s" title="%s" class="btn-new-top">%s</a>',
				$this->getNewUri(),
				$this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:newRecordGeneral'),
				IconUtility::getSpriteIcon('actions-document-new')
			);
		}


		return $output;
	}


	/**
	 * Render a create URI given a data type.
	 *
	 * @return string
	 */
	protected function getUriWizardNew() {
		$idParameter = '';
		if (GeneralUtility::_GP(Parameter::PID)) {
			$idParameter = sprintf('id=%s&', GeneralUtility::_GP(Parameter::PID));
		}
		$uri = sprintf('db_new.php?%sreturnUrl=%s',
			$idParameter,
			rawurlencode($this->getModuleLoader()->getModuleUrl())
		);
		return $uri;
	}

	/**
	 * Render a create URI given a data type.
	 *
	 * @return string
	 */
	protected function getNewUri() {
		return sprintf('alt_doc.php?returnUrl=%s&edit[%s][%s]=new',
			rawurlencode($this->getModuleLoader()->getModuleUrl()),
			rawurlencode($this->getModuleLoader()->getDataType()),
			$this->getStoragePid()
		);
	}

	/**
	 * Return the default configured pid.
	 *
	 * @return int
	 */
	protected function getStoragePid() {
		if (GeneralUtility::_GP(Parameter::PID)) {
			$pid = GeneralUtility::_GP(Parameter::PID);
		} elseif (TcaService::table()->get('rootLevel')) {
			$pid = 0;
		} else {
			// Get configuration from User TSconfig if any
			$tsConfigPath = sprintf('tx_vidi.dataType.%s.storagePid', $this->getModuleLoader()->getDataType());
			$result = $this->getBackendUser()->getTSConfig($tsConfigPath);
			$pid = $result['value'];

			// Get pid from Module Loader
			if (NULL === $pid) {
				$pid = $this->getModuleLoader()->getDefaultPid();
			}
		}
		return $pid;
	}

}
