<?php
namespace TYPO3\CMS\Vidi\ViewHelpers\Grid\Column;

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
			$output .= sprintf('Vidi._columns.push({ "mData": "%s", "bSortable": %s, "bVisible": %s, "sWidth": "%s", "sClass": "%s", "columnName": "%s" });' . PHP_EOL,
				$this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath), // Suitable field name for the DataTable plugin.
				TcaService::grid()->isSortable($fieldNameAndPath) ? 'true' : 'false',
				TcaService::grid()->isVisible($fieldNameAndPath) ? 'true' : 'false',
				TcaService::grid()->getWidth($fieldNameAndPath),
				TcaService::grid()->getClass($fieldNameAndPath),
				$fieldNameAndPath
			);
		}

		return $output;
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

		$isAllowed = FALSE;
		if (TcaService::grid()->isSystem($fieldNameAndPath)) {
			$isAllowed = TRUE; // @todo remove me in 0.6 + 2 versions
		} elseif (TcaService::grid()->hasRenderers($fieldNameAndPath)) {
			$isAllowed = TRUE;
		} elseif (TcaService::table()->field($fieldNameAndPath)->isSystem() || TcaService::table($dataType)->hasField($fieldName)) {
			$isAllowed = TRUE;
		}

		return $isAllowed;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}
}
