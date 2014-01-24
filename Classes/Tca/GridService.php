<?php
namespace TYPO3\CMS\Vidi\Tca;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException;
use TYPO3\CMS\Vidi\Grid\GenericRendererComponent;

/**
 * A class to handle TCA grid configuration
 */
class GridService implements \TYPO3\CMS\Vidi\Tca\TcaServiceInterface {

	/**
	 * @var array
	 */
	protected $tca;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * __construct
	 *
	 * @throws InvalidKeyInArrayException
	 * @param string $tableName
	 * @return \TYPO3\CMS\Vidi\Tca\GridService
	 */
	public function __construct($tableName) {

		$this->tableName = $tableName;

		if (empty($GLOBALS['TCA'][$this->tableName])) {
			throw new InvalidKeyInArrayException('No TCA existence for table name: ' . $this->tableName, 1356945108);
		}

		$this->tca = $GLOBALS['TCA'][$this->tableName]['grid'];
	}

	/**
	 * Returns an array containing column names.
	 *
	 * @return array
	 */
	public function getFieldNames() {
		return array_keys($this->tca['columns']);
	}

	/**
	 * Get the translation of a label given a column name.
	 *
	 * @param string $fieldName the name of the column
	 * @return string
	 */
	public function getLabel($fieldName) {
		$result = '';
		if ($this->hasLabel($fieldName)) {
			$field = $this->getField($fieldName);
			$result = LocalizationUtility::translate($field['label'], '');
			if (is_null($result)) {
				$result = $field['label'];
			}
		} elseif ($this->isNotSystem($fieldName) && TcaService::table($this->tableName)->field($fieldName)->hasLabel()) {
			$result = TcaService::table($this->tableName)->field($fieldName)->getLabel($fieldName);
		}
		return $result;
	}

	/**
	 * Tell whether the column is internal or not.
	 *
	 * @param string $fieldName the name of the column
	 * @return boolean
	 */
	public function isSystem($fieldName) {
		return strpos($fieldName, '__') === 0;
	}

	/**
	 * Returns the field name given its position.
	 *
	 * @param string $position the position of the field in the grid
	 * @throws InvalidKeyInArrayException
	 * @return int
	 */
	public function getFieldNameByPosition($position) {
		$fields = array_keys($this->getFields());
		if (empty($fields[$position])) {
			throw new InvalidKeyInArrayException('No field exist for position: ' . $position, 1356945119);
		}

		return $fields[$position];
	}

	/**
	 * Tell whether the column is not internal.
	 *
	 * @param string $fieldName the name of the column
	 * @return boolean
	 */
	public function isNotSystem($fieldName) {
		return !$this->isSystem($fieldName);
	}

	/**
	 * Returns an array containing the configuration of an column.
	 *
	 * @param string $fieldName the name of the column
	 * @return array
	 */
	public function getField($fieldName) {
		return $this->tca['columns'][$fieldName];
	}

	/**
	 * Returns an array containing column names.
	 *
	 * @return array
	 */
	public function getFields() {
		return is_array($this->tca['columns']) ? $this->tca['columns'] : array();
	}

	/**
	 * Tell whether the field exists in the grid or not.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasField($fieldName) {
		return isset($this->tca['columns'][$fieldName]);
	}

	/**
	 * Tell whether the field does not exist.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasNotField($fieldName) {
		return !$this->hasField($fieldName);
	}

	/**
	 * Tell whether the facet exists in the grid or not.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasFacet($fieldName) {
		return isset($this->tca['facets'][$fieldName]) || in_array($fieldName, $this->tca['facets']);
	}

	/**
	 * Tell whether the facet does not exist.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasNotFacet($fieldName) {
		return !$this->hasFacet($fieldName);
	}

	/**
	 * Returns an array containing facets fields.
	 *
	 * @return array
	 */
	public function getFacets() {
		return is_array($this->tca['facets']) ? $this->tca['facets'] : array();
	}

	/**
	 * Returns whether the column is sortable or not.
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function isSortable($fieldName) {
		$field = $this->getField($fieldName);
		return isset($field['sortable']) ? $field['sortable'] : TRUE;
	}

	/**
	 * Returns whether the column has a renderer.
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function hasRenderers($fieldName) {
		$field = $this->getField($fieldName);
		return empty($field['renderer']) && empty($field['renderers']) ? FALSE : TRUE;
	}

	/**
	 * Returns a renderer.
	 *
	 * @param string $fieldName the name of the column
	 * @return array
	 */
	public function getRenderers($fieldName) {
		$field = $this->getField($fieldName);
		$renderers = array();
		if (!empty($field['renderer'])) {
			$renderers = $this->convertRendererToArray($field['renderer']);
		} elseif (!empty($field['renderers']) && is_array($field['renderers'])) {
			foreach ($field['renderers'] as $renderer) {
				$renderers = $renderers + $this->convertRendererToArray($renderer);
			}
		}

		return $renderers;
	}

	/**
	 * @param string|GenericRendererComponent $renderer
	 * @return array
	 */
	public function convertRendererToArray($renderer) {
		$result = array();
		if (is_string($renderer)) {
			$result[$renderer] = array();
		} elseif ($renderer instanceof GenericRendererComponent) {
			/** @var GenericRendererComponent $renderer */
			$result[$renderer->getClassName()] = $renderer->getConfiguration();
		}
		return $result;
	}

	/**
	 * Returns whether the column is visible or not.
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function isVisible($fieldName) {
		$field = $this->getField($fieldName);
		return isset($field['visible']) ? $field['visible'] : TRUE;
	}

	/**
	 * Returns whether the column must be rendered.
	 * There is a mechanism that only necessary columns are rendered to improve performance.
	 * The "force" flag can by pass this mechanism.
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function isForce($fieldName) {
		$field = $this->getField($fieldName);
		return isset($field['force']) ? $field['force'] : FALSE;
	}

	/**
	 * Returns whether the column is editable or not.
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function isEditable($fieldName) {
		$field = $this->getField($fieldName);
		return isset($field['editable']) ? $field['editable'] : FALSE;
	}

	/**
	 * Returns the class names applied to a cell
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function getClass($fieldName) {
		$field = $this->getField($fieldName);
		return isset($field['class']) ? $field['class'] : '';
	}

	/**
	 * Returns the class names applied to a cell
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function getDataType($fieldName) {
		$field = $this->getField($fieldName);
		return isset($field['dataType']) ? $field['dataType'] : $this->tableName;
	}

	/**
	 * Returns whether the column has a label.
	 *
	 * @param string $fieldName the name of the column
	 * @return bool
	 */
	public function hasLabel($fieldName) {
		$field = $this->getField($fieldName);
		return empty($field['label']) ? FALSE : TRUE;
	}

	/**
	 * @return array
	 */
	public function getTca() {
		return $this->tca;
	}
}
?>