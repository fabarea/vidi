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

/**
 * A class to handle TCA field configuration.
 */
class FieldService implements TcaServiceInterface {

	/**
	 * @var string
	 */
	protected $fieldName;

	/**
	 * @var string
	 */
	protected $compositeField;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var array
	 */
	protected $tca;

	/**
	 * @param string $fieldName
	 * @param array $tca
	 * @param string $tableName
	 * @param string $compositeField
	 * @return \TYPO3\CMS\Vidi\Tca\FieldService
	 */
	public function __construct($fieldName, array $tca, $tableName, $compositeField = '') {
		$this->fieldName = $fieldName;
		$this->tca = $tca;
		$this->tableName = $tableName;
		$this->compositeField = $compositeField;
	}

	/**
	 * Tells whether the field is considered as system field, e.g. uid, crdate, tstamp, etc...
	 *
	 * @return bool
	 */
	public function isSystem() {
		return in_array($this->fieldName, TcaService::getSystemFields());
	}

	/**
	 * Tells the opposition of isSystem()
	 *
	 * @return bool
	 */
	public function isNotSystem() {
		return !$this->isSystem();
	}

	/**
	 * Returns the configuration for a $field
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getConfiguration() {
		return empty($this->tca['config']) ? array() : $this->tca['config'];
	}

	/**
	 * Returns a key of the configuration.
	 * If the key can not to be found, returns NULL.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		$configuration = $this->getConfiguration();
		return empty($configuration[$key]) ? NULL : $configuration[$key];
	}

	/**
	 * Returns the foreign field of a given field (opposite relational field).
	 * If no relation exists, returns NULL.
	 *
	 * @return string|NULL
	 */
	public function getForeignField() {
		$result = NULL;
		$configuration = $this->getConfiguration();

		if (!empty($configuration['foreign_field'])) {
			$result = $configuration['foreign_field'];
		} elseif ($this->hasRelationManyToMany()) {

			$foreignTable = $this->getForeignTable();
			$manyToManyTable = $this->getManyToManyTable();

			// Load TCA service of foreign field.
			$tcaForeignTableService = TcaService::table($foreignTable);

			// Look into the MM relations checking for the opposite field
			foreach ($tcaForeignTableService->getFields() as $fieldName) {
				if ($manyToManyTable == $tcaForeignTableService->field($fieldName)->getManyToManyTable()) {
					$result = $fieldName;
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Returns the foreign table of a given field (opposite relational table).
	 * If no relation exists, returns NULL.
	 *
	 * @return string|NULL
	 */
	public function getForeignTable() {
		$result = NULL;
		$configuration = $this->getConfiguration();

		if (!empty($configuration['foreign_table'])) {
			$result = $configuration['foreign_table'];
		} elseif ($this->isGroup()) {
			$fieldParts = explode('.', $this->compositeField, 2);
			$result = $fieldParts[1];
		}
		return $result;
	}

	/**
	 * Returns the foreign clause.
	 * If no foreign order exists, returns empty string.
	 *
	 * @return string
	 */
	public function getForeignClause() {
		$result = '';
		$configuration = $this->getConfiguration();

		if (!empty($configuration['foreign_table_where'])) {
			$parts = explode('ORDER BY', $configuration['foreign_table_where']);
			if (!empty($parts[0])) {
				$result = $parts[0];
			}
		}

		// Substitute some variables
		return $this->substituteKnownMarkers($result);
	}

	/**
	 * Substitute some known markers from the where clause in the Frontend Context.
	 *
	 * @param string $clause
	 * @return string
	 */
	protected function substituteKnownMarkers($clause) {
		if ($clause && $this->isFrontendMode()) {

			$searches = array(
				'###CURRENT_PID###',
				'###REC_FIELD_sys_language_uid###'
			);

			$replaces = array(
				$this->getFrontendObject()->id,
				$this->getFrontendObject()->sys_language_uid,
			);

			$clause = str_replace($searches, $replaces, $clause);
		}
		return $clause;
	}

	/**
	 * Returns the foreign order of the current field.
	 * If no foreign order exists, returns empty string.
	 *
	 * @return string
	 */
	public function getForeignOrder() {
		$result = '';
		$configuration = $this->getConfiguration();

		if (!empty($configuration['foreign_table_where'])) {
			$parts = explode('ORDER BY', $configuration['foreign_table_where']);
			if (!empty($parts[1])) {
				$result = $parts[1];
			}
		}
		return $result;
	}

	/**
	 * Returns the MM table of a field.
	 * If no relation exists, returns NULL.
	 *
	 * @return string|NULL
	 */
	public function getManyToManyTable() {
		$configuration = $this->getConfiguration();
		return empty($configuration['MM']) ? NULL : $configuration['MM'];
	}

	/**
	 * Returns a possible additional table name used in MM relations.
	 * If no table name exists, returns NULL.
	 *
	 * @return string|NULL
	 */
	public function getAdditionalTableNameCondition() {
		$result = NULL;
		$configuration = $this->getConfiguration();

		if (!empty($configuration['MM_match_fields']['tablenames'])) {
			$result = $configuration['MM_match_fields']['tablenames'];
		} elseif ($this->isGroup()) {

			// @todo check if $this->fieldName could be simply used as $result
			$fieldParts = explode('.', $this->compositeField, 2);
			$result = $fieldParts[1];
		}

		return $result;
	}

	/**
	 * Returns a possible additional conditions for MM tables such as "tablenames", "fieldname", etc...
	 *
	 * @return array
	 */
	public function getAdditionalMMCondition() {
		$additionalMMConditions = array();
		$configuration = $this->getConfiguration();

		if (!empty($configuration['MM_match_fields'])) {
			$additionalMMConditions = $configuration['MM_match_fields'];
		}

		// Add in any case a table name for "group"
		if ($this->isGroup()) {

			// @todo check if $this->fieldName could be simply used as $result
			$fieldParts = explode('.', $this->compositeField, 2);
			$additionalMMConditions = array(
				'tablenames' => $fieldParts[1],
			);
		}
		return $additionalMMConditions;
	}

	/**
	 * Returns whether the field name is the opposite in MM relation.
	 *
	 * @return bool
	 */
	public function isOppositeRelation() {
		$configuration = $this->getConfiguration();
		return isset($configuration['MM_opposite_field']);
	}

	/**
	 * Returns the configuration for a $field.
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function getType() {

		if ($this->isSystem()) {
			$fieldType = TcaService::NUMBER;
		} else {
			$configuration = $this->getConfiguration();

			if (empty($configuration['type'])) {
				throw new \Exception(sprintf('No field type found for "%s" in table "%s"', $this->fieldName, $this->tableName), 1385556627);
			}

			$fieldType = $configuration['type'];

			if ($configuration['type'] === TcaService::SELECT && !empty($configuration['size']) && $configuration['size'] > 1) {
				$fieldType = TcaService::MULTISELECT;
			} elseif (!empty($configuration['foreign_table'])
				&& ($configuration['foreign_table'] == 'sys_file_reference' || $configuration['foreign_table'] == 'sys_file')) {
				$fieldType = TcaService::FILE;
			} elseif (!empty($configuration['eval'])) {
				$parts = GeneralUtility::trimExplode(',', $configuration['eval']);
				if (in_array('datetime', $parts)) {
					$fieldType = TcaService::DATETIME;
				} elseif (in_array('date', $parts)) {
					$fieldType = TcaService::DATE;
				} elseif (in_array('email', $parts)) {
					$fieldType = TcaService::EMAIL;
				} elseif (in_array('int', $parts)) {
					$fieldType = TcaService::NUMBER;
				}
			}

			// Do some legacy conversion
			if ($fieldType === 'input') {
				$fieldType = TcaService::TEXT;
			} elseif ($fieldType === 'text') {
				$fieldType = TcaService::TEXTAREA;
			}
		}
		return $fieldType;
	}

	/**
	 * @return string
	 * @deprecated in 0.4.0, will be removed two version later.
	 */
	public function getFieldType() {
		return $this->getType();
	}

	/**
	 * Return the default value.
	 *
	 * @return bool
	 */
	public function getDefaultValue() {
		$configuration = $this->getConfiguration();
		return isset($configuration['default']) ? $configuration['default'] : NULL;
	}

	/**
	 * Get the translation of a label given a column.
	 *
	 * @return string
	 */
	public function getLabel() {
		$result = '';
		if ($this->hasLabel()) {
			$result = LocalizationUtility::translate($this->tca['label'], '');

			if (empty($result)) {
				$result = $this->tca['label'];
			}
		}
		return $result;
	}

	/**
	 * Get the translation of a label given a column.
	 *
	 * @param string $itemValue the item value to search for.
	 * @return string
	 */
	public function getLabelForItem($itemValue) {

		// Early return whether there is nothing to be translated as label.
		if (is_null($itemValue)) {
			return '';
		} elseif (is_string($itemValue) && $itemValue === '') {
			return $itemValue;
		}

		$configuration = $this->getConfiguration();
		if (!empty($configuration['items']) && is_array($configuration['items'])) {
			foreach ($configuration['items'] as $item) {
				if ($item[1] == $itemValue) {
					$label = LocalizationUtility::translate($item[0], '');
					if (empty($label)) {
						$label = $item[0];
					}
					break;
				}
			}
		}

		// Try fetching a label from a possible itemsProcFunc
		if (!isset($label)) {
			$items = $this->fetchItemsFromUserFunction();
			if (!empty($items[$itemValue])) {
				$label = $items[$itemValue];
			}
		}

		// Returns a label if it has been found, otherwise returns the item value as fallback.
		return isset($label) ? $label : $itemValue;
	}

	/**
	 * Retrieve items from User Function.
	 *
	 * @return array
	 */
	protected function fetchItemsFromUserFunction() {
		$values = array();

		$configuration = $this->getConfiguration();
		if (!empty($configuration['itemsProcFunc'])) {
			$parts = explode('php:', $configuration['itemsProcFunc']);
			if (!empty($parts[1])) {

				list($class, $method) = explode('->', $parts[1]);

				$parameters['items'] = array();
				$object = GeneralUtility::makeInstance($class);
				$object->$method($parameters);

				foreach ($parameters['items'] as $items) {
					$values[$items[1]] = $items[0];
				}
			}
		}
		return $values;
	}

	/**
	 * Get a possible icon given a field name an an item.
	 *
	 * @param string $itemValue the item value to search for.
	 * @return string
	 */
	public function getIconForItem($itemValue) {
		$result = '';
		$configuration = $this->getConfiguration();
		if (!empty($configuration['items']) && is_array($configuration['items'])) {
			foreach ($configuration['items'] as $item) {
				if ($item[1] == $itemValue) {
					$result = empty($item[2]) ? '' : $item[2];
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Returns whether the field has a label.
	 *
	 * @return bool
	 */
	public function hasLabel() {
		return empty($this->tca['label']) ? FALSE : TRUE;
	}

	/**
	 * Returns whether the field is numerical.
	 *
	 * @return bool
	 */
	public function isNumerical() {
		$result = $this->isSystem();
		if ($result === FALSE) {
			$configuration = $this->getConfiguration();
			$parts = array();
			if (!empty($configuration['eval'])) {
				$parts = GeneralUtility::trimExplode(',', $configuration['eval']);
			}
			$result = in_array('int', $parts) || in_array('float', $parts);
		}
		return $result;
	}

	/**
	 * Returns whether the field is of type text area.
	 *
	 * @return bool
	 */
	public function isTextArea() {
		return $this->getType() === TcaService::TEXTAREA;
	}

	/**
	 * Returns whether the field is of type select.
	 *
	 * @return bool
	 */
	public function isSelect() {
		return $this->getType() === TcaService::SELECT;
	}

	/**
	 * Returns whether the field is of type select.
	 *
	 * @return bool
	 */
	public function isCheckBox() {
		return $this->getType() === TcaService::CHECKBOX;
	}

	/**
	 * Returns whether the field is of type db.
	 *
	 * @return bool
	 */
	public function isGroup() {
		return $this->getType() === 'group';
	}

	/**
	 * Returns whether the field is language aware.
	 *
	 * @return bool
	 */
	public function isLocalized() {
		$isLocalized = FALSE;
		if (isset($this->tca['l10n_mode'])) {

			if ($this->tca['l10n_mode'] == 'prefixLangTitle' || $this->tca['l10n_mode'] == 'mergeIfNotBlank') {
				$isLocalized = TRUE;
			}
		}
		return $isLocalized;
	}

	/**
	 * Returns whether the field is required.
	 *
	 * @return bool
	 */
	public function isRequired() {
		$configuration = $this->getConfiguration();

		$isRequired = FALSE;
		if (isset($configuration['minitems'])) {
			// is required of a select?
			$isRequired = $configuration['minitems'] == 1 ? TRUE : FALSE;
		} elseif (isset($configuration['eval'])) {
			$parts = GeneralUtility::trimExplode(',', $configuration['eval'], TRUE);
			$isRequired = in_array('required', $parts);
		}
		return $isRequired;
	}

	/**
	 * Returns an array containing the configuration of a column.
	 *
	 * @return array
	 */
	public function getField() {
		return $this->tca;
	}

	/**
	 * Returns the relation type
	 *
	 * @return string
	 */
	public function relationDataType() {
		$configuration = $this->getConfiguration();
		return empty($configuration['foreign_table']) ? '' : $configuration['foreign_table'];
	}

	/**
	 * Returns whether the field has relation (one to many, many to many)
	 *
	 * @return bool
	 */
	public function hasRelation() {
		return NULL !== $this->getForeignTable();
	}

	/**
	 * Returns whether the field has no relation (one to many, many to many)
	 *
	 * @return bool
	 */
	public function hasNoRelation() {
		return !$this->hasRelation();
	}

	/**
	 * Returns whether the field has a "many" objects connected including "many-to-many" or "one-to-many".
	 *
	 * @return bool
	 */
	public function hasMany() {
		$configuration = $this->getConfiguration();
		return $this->hasRelation() && ($configuration['maxitems'] > 1 || isset($configuration['foreign_table_field']));
	}

	/**
	 * Returns whether the field has relation "one" object connected including of "one-to-one" or "many-to-one".
	 *
	 * @return bool
	 */
	public function hasOne() {
		$configuration = $this->getConfiguration();
		return $this->hasRelation() && $configuration['maxitems'] == 1;
	}

	/**
	 * Returns whether the field has many-to-one relation.
	 *
	 * @return bool
	 */
	public function hasRelationManyToOne() {
		$result = FALSE;

		$foreignField = $this->getForeignField();
		if (!empty($foreignField)) {

			// Load TCA service of the foreign field.
			$foreignTable = $this->getForeignTable();
			$result = $this->hasOne() && TcaService::table($foreignTable)->field($foreignField)->hasMany();
		}
		return $result;
	}

	/**
	 * Returns whether the field has one-to-many relation.
	 *
	 * @return bool
	 */
	public function hasRelationOneToMany() {
		$result = FALSE;

		$foreignField = $this->getForeignField();
		if (!empty($foreignField)) {

			// Load TCA service of the foreign field.
			$foreignTable = $this->getForeignTable();
			$result = $this->hasMany() && TcaService::table($foreignTable)->field($foreignField)->hasOne();
		}
		return $result;
	}

	/**
	 * Returns whether the field has one-to-one relation.
	 *
	 * @return bool
	 */
	public function hasRelationOneToOne() {
		$result = FALSE;

		$foreignField = $this->getForeignField();
		if (!empty($foreignField)) {

			// Load TCA service of foreign field.
			$foreignTable = $this->getForeignTable();
			$result = $this->hasOne() && TcaService::table($foreignTable)->field($foreignField)->hasOne();
		}
		return $result;
	}

	/**
	 * Returns whether the field has many to many relation.
	 *
	 * @return bool
	 */
	public function hasRelationManyToMany() {
		$configuration = $this->getConfiguration();
		return $this->hasRelation() && (isset($configuration['MM']) || isset($configuration['foreign_table_field']));
	}

	/**
	 * Returns whether the field has many to many relation using comma separated values (legacy).
	 *
	 * @return bool
	 */
	public function hasRelationWithCommaSeparatedValues() {
		$configuration = $this->getConfiguration();
		return $this->hasRelation() && !isset($configuration['MM']) && !isset($configuration['foreign_field']) && $configuration['maxitems'] > 1;
	}

	/**
	 * @return array
	 */
	public function getTca() {
		return $this->tca['columns'];
	}

	/**
	 * @return string
	 */
	public function getCompositeField() {
		return $this->compositeField;
	}

	/**
	 * @param string $compositeField
	 */
	public function setCompositeField($compositeField) {
		$this->compositeField = $compositeField;
	}

	/**
	 * Returns whether the current mode is Frontend
	 *
	 * @return bool
	 */
	protected function isFrontendMode() {
		return TYPO3_MODE == 'FE';
	}

	/**
	 * Returns an instance of the Frontend object.
	 *
	 * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected function getFrontendObject() {
		return $GLOBALS['TSFE'];
	}


}
