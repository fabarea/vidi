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

use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Class rendering relation
 */
class RelationRenderer extends GridRendererAbstract {

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

		if ($table->field($this->fieldName)->hasOne()) {

			$foreignObject = $this->object[$this->fieldName];

			if ($foreignObject) {
				$template = '<a href="%s" data-uid="%s" class="btn-edit invisible">%s</a><span>%s</span>';
				$result = sprintf($template,
					$this->getEditUri($foreignObject),
					$this->object->getUid(),
					IconUtility::getSpriteIcon('actions-document-open'),
					$foreignObject[$foreignLabelField]
				);
			}
		} elseif ($table->field($this->fieldName)->hasMany()) {

			if (!empty($this->object[$this->fieldName])) {
				$template = '<li><a href="%s" data-uid="%s" class="btn-edit invisible">%s</a><span>%s</span></li>';

				/** @var $foreignObject \TYPO3\CMS\Vidi\Domain\Model\Content */
				foreach ($this->object[$this->fieldName] as $foreignObject) {
					$result .= sprintf($template,
						$this->getEditUri($foreignObject),
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
	 * Render an edit URI given an object.
	 *
	 * @param Content $object
	 * @return string
	 */
	protected function getEditUri(Content $object) {
		return sprintf('alt_doc.php?returnUrl=%s&edit[%s][%s]=edit',
			rawurlencode($this->getModuleLoader()->getModuleUrl()),
			rawurlencode($object->getDataType()),
			$object->getUid()
		);
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
