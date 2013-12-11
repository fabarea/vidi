<?php
namespace TYPO3\CMS\Vidi\GridRenderer;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class rendering relation
 */
class RelationCount extends GridRendererAbstract {

	/**
	 * @var \TYPO3\CMS\Vidi\ViewHelpers\Uri\EditViewHelper
	 */
	protected $editViewHelper;

	/**
	 * @var \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
	 */
	protected $translateViewHelper;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->editViewHelper = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ViewHelpers\Uri\EditViewHelper');
		$this->translateViewHelper = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper');
	}

	/**
	 * Render a representation of the relation on the GUI.
	 *
	 * @return string
	 */
	public function render() {

		// Get TCA Field service
		$tcaTableService = TcaService::table($this->object->getDataType());

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

		$template = '<a href="/typo3/mod.php?M=%s&returnUrl=%s&search=%s&query=%s:%s">%s %s<span class="invisible" style="padding-left: 5px">%s</span></a>';

		$search = json_encode(array(array($tcaTableService->field($this->fieldName)->getForeignField() => $this->object->getUid())));

		// @todo naive implementation. Better to base64 encode? Remove todo if no complain...
		$search = str_replace('"', "'", $search);
		return sprintf($template,
			empty($this->gridRendererConfiguration['targetModule']) ? '' : $this->gridRendererConfiguration['targetModule'],
			'/typo3/mod.php?M=' . $this->gridRendererConfiguration['sourceModule'],
			$search,
			$tcaTableService->field($this->fieldName)->getForeignField(),
			$this->object->getUid(),
			$numberOfObjects,
			LocalizationUtility::translate($label, ''),
			IconUtility::getSpriteIcon('extensions-vidi-go')
		);
	}
}
?>