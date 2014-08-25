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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class rendering relation
 */
class RelationCountRenderer extends GridRendererAbstract {

	/**
	 * @var \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
	 */
	protected $translateViewHelper;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->translateViewHelper = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper');
	}

	/**
	 * Render a representation of the relation on the GUI.
	 *
	 * @return string
	 */
	public function render() {

		$numberOfObjects = count($this->object[$this->fieldName]);

		if ($numberOfObjects > 1) {
			$label = 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:items';
			if (isset($this->gridRendererConfiguration['labelPlural'])) {
				$label = $this->gridRendererConfiguration['labelPlural'];
			}
		} else {
			$label = 'LLL:EXT:vidi/Resources/Private/Language/locallang.xlf:item';
			if (isset($this->gridRendererConfiguration['labelSingular'])) {
				$label = $this->gridRendererConfiguration['labelSingular'];
			}
		}

		$template = '<a href="%s&returnUrl=%s&search=%s&query=%s:%s">%s %s<span class="invisible" style="padding-left: 5px">%s</span></a>';

		$foreignField = TcaService::table($this->object)->field($this->fieldName)->getForeignField();
		$search = json_encode(array(array($foreignField => $this->object->getUid())));

		$moduleTarget = empty($this->gridRendererConfiguration['targetModule']) ? '' : $this->gridRendererConfiguration['targetModule'];
		return sprintf($template,
			BackendUtility::getModuleUrl($moduleTarget),
			rawurlencode(BackendUtility::getModuleUrl($this->gridRendererConfiguration['sourceModule'])),
			rawurlencode($search),
			rawurlencode($foreignField),
			rawurlencode($this->object->getUid()),
			htmlspecialchars($numberOfObjects),
			htmlspecialchars(LocalizationUtility::translate($label, '')),
			IconUtility::getSpriteIcon('extensions-vidi-go')
		);
	}
}
