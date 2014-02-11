<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid;
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Exception\NotExistingFieldException;
use TYPO3\CMS\Vidi\Tca\TcaService;
use TYPO3\CMS\Vidi\Tca\TcaServiceFactory;

/**
 * View helper for rendering configuration that will be consumed by Javascript
 */
class ConfigurationViewHelper extends AbstractViewHelper {

	/**
	 * Render the columns of the grid.
	 *
	 * @throws NotExistingFieldException
	 * @return string
	 */
	public function render() {
		$output = '';

		foreach(TcaService::grid()->getFields() as $fieldName => $configuration) {

			// Early failure if field does not exist.
			if (!$this->isAllowed($fieldName)) {
				$message = sprintf('Property "%s" does not exist!', $fieldName);
				throw new NotExistingFieldException($message, 1375369594);
			}

			$output .= sprintf('Vidi._columns.push({ "mData": "%s", "bSortable": %s, "bVisible": %s, "sWidth": "%s", "sClass": "%s %s", "dataType": "%s" });' . PHP_EOL,
				$fieldName,
				TcaService::grid()->isSortable($fieldName) ? 'true' : 'false',
				TcaService::grid()->isVisible($fieldName) ? 'true' : 'false',
				empty($configuration['width']) ? 'auto' : $configuration['width'],
				$this->computeEditableClass($fieldName),
				TcaService::grid()->getClass($fieldName),
				TcaService::grid()->getDataType($fieldName)
			);
		}

		return $output;
	}

	/**
	 * Return the editable class name for jeditable plugin.
	 *
	 * @param string $fieldName
	 * @return boolean
	 */
	protected function computeEditableClass($fieldName) {
		$result = FALSE;
		if (TcaService::grid()->isEditable($fieldName)) {
			$dataType = TcaService::grid()->getDataType($fieldName);
			$result = TcaService::table($dataType)->field($fieldName)->isTextArea() ? 'editable-textarea' : 'editable-textfield';
		}
		return $result;
	}

	/**
	 * Tell whether the field looks ok to be displayed within the Grid.
	 *
	 * @param string $fieldName
	 * @return boolean
	 */
	protected function isAllowed($fieldName){

		$result = FALSE;
		if (TcaService::grid()->isSystem($fieldName)
			|| TcaService::grid()->hasRenderers($fieldName)
			|| TcaService::table()->field($fieldName)->isSystem()
			|| TcaService::table()->hasField($fieldName)
		) {
			$result = TRUE;
		}
		return $result;
	}

}
