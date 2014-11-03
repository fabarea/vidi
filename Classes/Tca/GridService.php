<?php
namespace TYPO3\CMS\Vidi\Tca;

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException;
use TYPO3\CMS\Vidi\Facet\StandardFacet;
use TYPO3\CMS\Vidi\Facet\FacetInterface;
use TYPO3\CMS\Vidi\Grid\GenericRendererComponent;

/**
 * A class to handle TCA grid configuration
 */
class GridService implements TcaServiceInterface {

	/**
	 * @var array
	 */
	protected $tca;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var array
	 */
	protected $instances;

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
	 * @param string $fieldNameAndPath
	 * @return string
	 */
	public function getLabel($fieldNameAndPath) {
		$label = '';
		if ($this->hasLabel($fieldNameAndPath)) {
			$field = $this->getField($fieldNameAndPath);
			$label = LocalizationUtility::translate($field['label'], '');
			if (is_null($label)) {
				$label = $field['label'];
			}
		} else {

			// Important to notice the label can contains a path, e.g. metadata.categories and must be resolved.
			$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $this->tableName);
			$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $this->tableName);
			$table = TcaService::table($dataType);

			if ($table->hasField($fieldName) && $table->field($fieldName)->hasLabel()) {
				$label = $table->field($fieldName)->getLabel();
			}
		}
		return $label;
	}

	/**
	 * Tell whether the column is internal or not.
	 *
	 * @param string $fieldName
	 * @return boolean
	 * @deprecated will be removed in 0.6 + 2 versions.
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
	 * @param string $fieldName
	 * @return boolean
	 * @deprecated will be removed in 0.6 + 2 versions.
	 */
	public function isNotSystem($fieldName) {
		return !$this->isSystem($fieldName);
	}

	/**
	 * Returns an array containing the configuration of an column.
	 *
	 * @param string $fieldName
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
	 * @param string $facetName
	 * @return bool
	 */
	public function hasFacet($facetName) {

		$hasFacet = FALSE;
		foreach ($this->getFacets() as $facet) {
			if ($facet instanceof FacetInterface) {
				$facet = $facet->getName();
			}

			if ($facet === $facetName) {
				$hasFacet = TRUE;
				break;
			}
		}

		return $hasFacet;
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
	 * Returns the "sortable" value of the column.
	 *
	 * @param string $fieldName
	 * @return int|string
	 */
	public function isSortable($fieldName) {
		$defaultValue = TRUE;
		return $this->get($fieldName, 'sortable', $defaultValue);
	}

	/**
	 * Returns the "canBeHidden" value of the column.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function canBeHidden($fieldName) {
		$defaultValue = TRUE;
		return $this->get($fieldName, 'canBeHidden', $defaultValue);
	}

	/**
	 * Returns the "width" value of the column.
	 *
	 * @param string $fieldName
	 * @return int|string
	 */
	public function getWidth($fieldName) {
		$defaultValue = 'auto';
		return $this->get($fieldName, 'width', $defaultValue);
	}

	/**
	 * Returns the "visible" value of the column.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function isVisible($fieldName) {
		$defaultValue = TRUE;
		return $this->get($fieldName, 'visible', $defaultValue);
	}

	/**
	 * Returns the "editable" value of the column.
	 *
	 * @param string $columnName
	 * @return bool
	 */
	public function isEditable($columnName) {
		$defaultValue = FALSE;
		return $this->get($columnName, 'editable', $defaultValue);
	}

	/**
	 * Returns the "localized" value of the column.
	 *
	 * @param string $columnName
	 * @return bool
	 */
	public function isLocalized($columnName) {
		$defaultValue = TRUE;
		return $this->get($columnName, 'localized', $defaultValue);
	}

	/**
	 *
	 * Returns the "html" value of the column.
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public function getHeader($fieldName) {
		$defaultValue = '';
		return $this->get($fieldName, 'html', $defaultValue);
	}

	/**
	 * Fetch a possible from a Grid Renderer. If no value is found, returns NULL
	 *
	 * @param string $fieldName
	 * @param string $key
	 * @param mixed $defaultValue
	 * @return NULL|mixed
	 */
	public function get($fieldName, $key, $defaultValue = NULL) {
		$value = $defaultValue;

		$field = $this->getField($fieldName);
		if (isset($field[$key])) {
			$value = $field[$key];
		} elseif ($this->hasRenderers($fieldName)) {
			$renderers = $this->getRenderers($fieldName);
			foreach ($renderers as $rendererConfiguration) {
				if (isset($rendererConfiguration[$key])) {
					$value = $rendererConfiguration[$key];
				}
			}
		}
		return $value;
	}

	/**
	 * Returns whether the column has a renderer.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRenderers($fieldName) {
		$field = $this->getField($fieldName);
		return empty($field['renderer']) && empty($field['renderers']) ? FALSE : TRUE;
	}

	/**
	 * Returns a renderer.
	 *
	 * @param string $fieldName
	 * @return array
	 */
	public function getRenderers($fieldName) {
		$field = $this->getField($fieldName);
		$renderers = array();
		if (!empty($field['renderer'])) {
			$renderers = $this->convertRendererToArray($field['renderer']);
		} elseif (!empty($field['renderers']) && is_array($field['renderers'])) {
			foreach ($field['renderers'] as $renderer) {
				$rendererNameAndConfiguration = $this->convertRendererToArray($renderer);
				$renderers = array_merge($renderers, $rendererNameAndConfiguration);
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
	 * Returns the class names applied to a cell
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function getClass($fieldName) {
		$field = $this->getField($fieldName);
		return isset($field['class']) ? $field['class'] : '';
	}

	/**
	 * Returns whether the column has a label.
	 *
	 * @param string $fieldName
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

	/**
	 * @return array
	 */
	public function getExcludedFields() {
		$excludedFields = array();
		if (!empty($this->tca['export']['excluded_fields'])) {
			$excludedFields = GeneralUtility::trimExplode(',', $this->tca['export']['excluded_fields'], TRUE);

		}
		return $excludedFields;
	}

	/**
	 * @return array
	 */
	public function areFilesIncludedInExport() {
		$isIncluded = TRUE;

		if (isset($this->tca['export']['include_files'])) {
			$isIncluded = $this->tca['export']['include_files'];
		}
		return $isIncluded;
	}

	/**
	 * Returns a "facet" service instance.
	 *
	 * @param string|FacetInterface $facet
	 * @return \TYPO3\CMS\Vidi\Tca\FacetService
	 */
	public function facet($facet = '') {

		if (!$facet instanceof StandardFacet) {
			$label = TcaService::grid($this->tableName)->getLabel($facet);

			/** @var StandardFacet $facet */
			$facet = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Facet\StandardFacet', $facet, $label);
		}

		if (empty($this->instances[$facet->getName()])) {

			/** @var \TYPO3\CMS\Vidi\Tca\FacetService $instance */
			$instance = GeneralUtility::makeInstance(
				'TYPO3\CMS\Vidi\Tca\FacetService',
				$facet,
				$this->tableName
			);

			$this->instances[$facet->getName()] = $instance;
		}

		return $this->instances[$facet->getName()];
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}

}
