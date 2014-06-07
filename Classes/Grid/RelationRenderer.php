<?php
namespace TYPO3\CMS\Vidi\Grid;
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
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class rendering relation
 */
class RelationRenderer extends GridRendererAbstract {

	/**
	 * @var \TYPO3\CMS\Vidi\ViewHelpers\Uri\EditViewHelper
	 */
	protected $editViewHelper;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->editViewHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ViewHelpers\Uri\EditViewHelper');
	}

	/**
	 * Render a representation of the relation on the GUI.
	 *
	 * @return string
	 */
	public function render() {

		$result = '';

		// Get TCA table service.
		$table = TcaService::table($this->object);

		// Get label of the foreign table.
		$foreignLabelField = $this->getForeignTableLabelField($this->fieldName);

		if ($table->field($this->fieldName)->hasRelationOne()) {

			$foreignObject = $this->object[$this->fieldName];

			if ($foreignObject) {
				$template = '<a href="%s" data-uid="%s" class="btn-edit invisible">%s</a><span>%s</span>';
				$result = sprintf($template,
					$this->editViewHelper->render($foreignObject),
					$this->object->getUid(),
					IconUtility::getSpriteIcon('actions-document-open'),
					$foreignObject[$foreignLabelField]
				);
			}
		} elseif ($table->field($this->fieldName)->hasRelationMany()) {

			if (!empty($this->object[$this->fieldName])) {
				$template = '<li><a href="%s" data-uid="%s" class="btn-edit invisible">%s</a><span>%s</span></li>';

				/** @var $foreignObject \TYPO3\CMS\Vidi\Domain\Model\Content */
				foreach ($this->object[$this->fieldName] as $foreignObject) {
					$result .= sprintf($template,
						$this->editViewHelper->render($foreignObject),
						$this->object->getUid(),
						IconUtility::getSpriteIcon('actions-document-open'),
						$foreignObject[$foreignLabelField]);
				}
				$result = sprintf('<ul class="unstyled">%s</ul>', $result);
			}
		}
		return $result;
	}

	/**
	 * Return the label field of the foreign table.
	 *
	 * @param string $fieldName
	 * @return string
	 */
	protected function getForeignTableLabelField($fieldName) {

		// Get TCA table service.
		$table = TcaService::table($this->object);

		// Compute the label of the foreign table.
		$relationDataType = $table->field($fieldName)->relationDataType();
		return TcaService::table($relationDataType)->getLabelField();
	}
}
