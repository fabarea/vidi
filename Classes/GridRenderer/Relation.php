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
use TYPO3\CMS\Vidi\Tca\TcaServiceFactory;

/**
 * Class rendering relation
 */
class Relation implements \TYPO3\CMS\Vidi\GridRenderer\GridRendererInterface {

	/**
	 * @var \TYPO3\CMS\Vidi\ViewHelpers\Link\EditViewHelper
	 */
	protected $editViewHelper;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->editViewHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ViewHelpers\Link\EditViewHelper');
	}

	/**
	 * Render a representation of the relation on the GUI.
	 *
	 * @param \TYPO3\CMS\Vidi\Domain\Model\Content $content
	 * @param string $fieldName
	 * @param array $configuration
	 * @return string
	 */
	public function render(\TYPO3\CMS\Vidi\Domain\Model\Content $content = NULL, $fieldName = NULL, $configuration = array()) {

		$result = '';

		// Get TCA Field service
		$tcaFieldService = TcaServiceFactory::getFieldService($content->getDataType());

		// Get label of the foreign table
		$foreignLabelField = $this->getForeignTableLabelField($fieldName);

		if ($tcaFieldService->hasRelationOne($fieldName)) {

			$foreignObject = $content[$fieldName];

			if ($foreignObject) {
				$template = '<a href="%s" data-uid="%s" class="btn-edit invisible">%s</a><span>%s</span>';
				$result = sprintf($template,
					$this->editViewHelper->render($foreignObject),
					$content->getUid(),
					\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-open'),
					$foreignObject[$foreignLabelField]
				);
			}
		} elseif ($tcaFieldService->hasRelationMany($fieldName)) {

			if (!empty($content[$fieldName])) {
				$template = '<li><a href="%s" data-uid="%s" class="btn-edit invisible">%s</a><span>%s</span></li>';

				/** @var $foreignObject \TYPO3\CMS\Vidi\Domain\Model\Content */
				foreach ($content[$fieldName] as $foreignObject) {
					$result .= sprintf($template,
						$this->editViewHelper->render($foreignObject),
						$content->getUid(),
						\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('actions-document-open'),
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
	 * @param string $propertyName
	 * @return string
	 */
	public function getForeignTableLabelField($propertyName) {

		// Compute the label of the foreign table.
		$relationDataType = TcaServiceFactory::getFieldService()->relationDataType($propertyName);
		return TcaServiceFactory::getTableService($relationDataType)->getLabelField();
	}
}
?>