<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid\Column;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Vidi\Exception\NotExistingFieldException;
use TYPO3\CMS\Vidi\Tca\TcaService;

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

		foreach(TcaService::grid()->getFields() as $fieldNameAndPath => $configuration) {

			// Early failure if field does not exist.
			if (!$this->isAllowed($fieldNameAndPath)) {
				$message = sprintf('Property "%s" does not exist!', $fieldNameAndPath);
				throw new NotExistingFieldException($message, 1375369594);
			}

			// mData vs columnName
			// -------------------
			// mData: internal name of DataTable plugin and can not contains a path, e.g. metadata.title
			// columnName: whole field name with path
			$output .= sprintf('Vidi._columns.push({ "mData": "%s", "bSortable": %s, "bVisible": %s, "sWidth": "%s", "sClass": "%s %s", "columnName": "%s" });' . PHP_EOL,
				$this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath), // Suitable field name for the DataTable plugin.
				TcaService::grid()->isSortable($fieldNameAndPath) ? 'true' : 'false',
				TcaService::grid()->isVisible($fieldNameAndPath) ? 'true' : 'false',
				empty($configuration['width']) ? 'auto' : $configuration['width'],
				$this->computeEditableClass($fieldNameAndPath),
				TcaService::grid()->getClass($fieldNameAndPath),
				$fieldNameAndPath
			);
		}

		return $output;
	}

	/**
	 * Return the editable class name for jeditable plugin.
	 *
	 * @param string $fieldNameAndPath
	 * @return boolean
	 */
	protected function computeEditableClass($fieldNameAndPath) {
		$result = FALSE;
		$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
		$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

		if (TcaService::grid()->isEditable($fieldNameAndPath)
			&& TcaService::table($dataType)->hasField($fieldName)
			&& TcaService::table($dataType)->field($fieldName)->hasNoRelation() // relation are editable through Renderer only.
		) {
			$result = TcaService::table($dataType)->field($fieldName)->isTextArea() ? 'editable-textarea' : 'editable-textfield';
		}
		return $result;
	}

	/**
	 * Tell whether the field looks ok to be displayed within the Grid.
	 *
	 * @param string $fieldNameAndPath
	 * @return boolean
	 */
	protected function isAllowed($fieldNameAndPath){
		$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
		$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

		return TcaService::grid()->isSystem($fieldNameAndPath)
			|| TcaService::grid()->hasRenderers($fieldNameAndPath)
			|| TcaService::table()->field($fieldNameAndPath)->isSystem()
			|| TcaService::table($dataType)->hasField($fieldName);
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver () {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}
}
