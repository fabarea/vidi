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

/**
 * A class to handle TCA field configuration.
 */
class ColumnService implements \TYPO3\CMS\Vidi\Tca\TcaServiceInterface {

	/**
	 * @var string
	 */
	protected $fieldName;

	/**
	 * @var string
	 */
	protected $fieldNameAndPath;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var array
	 */
	protected $tca;

	/**
	 * @var array
	 */
	protected $systemFields = array(
		'uid',
		'tstamp',
		'crdate',
		'deleted',
		'hidden',
		'startime',
		'endtime',
		'sys_language_uid',
		'l18n_parent',
		'l18n_diffsource',
		't3ver_oid',
		't3ver_id',
		't3ver_wsid',
		't3ver_label',
		't3ver_state',
		't3ver_stage',
		't3ver_count',
		't3ver_tstamp',
		't3_origuid',
	);

	/**
	 * @param string $fieldName
	 * @param array $tca
	 * @param string $tableName
	 * @param string $fieldNameAndPath
	 * @return \TYPO3\CMS\Vidi\Tca\ColumnService
	 */
	public function __construct($fieldName, array $tca, $tableName, $fieldNameAndPath = '') {
		$this->fieldName = $fieldName;
		$this->tca = $tca;
		$this->tableName = $tableName;
		$this->fieldNameAndPath = $fieldNameAndPath;
	}

	/**
	 * Tells whether the field is considered as system field, e.g. uid, crdate, tstamp, etc...
	 *
	 * @return bool
	 */
	public function isSystem() {
		return in_array($this->fieldName, $this->systemFields);
	}

	/**
	 * Returns the configuration for a $field
	 *
	 * @throws \Exception
	 * @return array
	 */
	public function getConfiguration() {

		if (empty($this->tca['config'])) {
			throw new \Exception(sprintf('No field configuration found for field "%s" in table "%s"', $this->fieldName, $this->tableName), 1385408686);
		}
		return $this->tca['config'];
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
		} elseif ($this->hasRelationManyToMany($fieldName)) {

			$foreignTable = $this->getForeignTable($fieldName);
			$manyToManyTable = $this->getManyToManyTable($fieldName);

			// Load TCA service of foreign field.
			$tcaForeignFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($foreignTable);
			#to replace: $tcaForeignFieldService = TcaService::table($foreignTable);

			// Look into the MM relations checking for the opposite field
			foreach ($tcaForeignFieldService->getFields() as $fieldName) {
				# to replace: TcaService::table($foreignTable)->field($fieldName)->getManyToManyTable();
				if ($manyToManyTable == $tcaForeignFieldService->getManyToManyTable($fieldName)) {
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
			$fieldParts = explode('.', $this->fieldNameAndPath, 2);
			$result = $fieldParts[1];
		}
		return $result;
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
	 * Returns the a possible additional table name used in MM relations.
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

			// @todo
			$fieldParts = explode('.', $fieldName, 2);
			$result = $fieldParts[1];
		}

		return $result;
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
	public function getFieldType() {
		$configuration = $this->getConfiguration();

		if (empty($configuration['type'])) {
			throw new \Exception('No field type found for "%s" in table "%s"', $this->fieldName, $this->tableName, 1385556627);
		}

		$result = $configuration['type'];

		if ($configuration['type'] === TcaService::SELECT && !empty($configuration['size']) && $configuration['size'] > 1) {
			$result = TcaService::MULTI_SELECT;
		}

		if (!empty($configuration['eval'])) {
			$parts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['eval']);
			if (in_array('datetime', $parts)) {
				$result = TcaService::DATE_TIME;
			} elseif (in_array('date', $parts)) {
				$result = TcaService::DATE;
			} elseif (in_array('int', $parts)) {
				$result = TcaService::NUMBER;
			}
		}
		return $result;
	}

	/**
	 * Get the translation of a label given a column.
	 *
	 * @return string
	 */
	public function getLabel() {
		$result = '';
		if ($this->hasLabel()) {
			$result = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->tca['label'], '');
		}
		return $result;
	}

	/**
	 * Get the translation of a label given a column.
	 *
	 * @param string $itemValue the item value to search for.
	 * @return string
	 */
	public function getLabelForItem($fieldName, $itemValue) {
		$result = '';
		$configuration = $this->getConfiguration();
		if (!empty($configuration['items']) && is_array($configuration['items'])) {
			foreach ($configuration['items'] as $item) {
				if ($item[1] == $itemValue) {
					$result = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($item[0], '');
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Get a possible icon given a field name an an item.
	 *
	 * @param string $itemValue the item value to search for.
	 * @return string
	 */
	public function getIconForItem($fieldName, $itemValue) {
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
		$result = in_array($fieldName, array('uid', 'pid'));
		if ($result === FALSE) {
			$configuration = $this->getConfiguration();
			$parts = array();
			if (!empty($configuration['eval'])) {
				$parts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['eval']);
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
		return $this->getFieldType() === 'text';
	}

	/**
	 * Returns whether the field is of type select.
	 *
	 * @return bool
	 */
	public function isSelect() {
		return $this->getFieldType() === 'select';
	}

	/**
	 * Returns whether the field is of type db.
	 *
	 * @return bool
	 */
	public function isGroup() {
		return $this->getFieldType() === 'group';
	}

	/**
	 * Returns whether the field is required.
	 *
	 * @return bool
	 */
	public function isRequired() {
		$configuration = $this->getConfiguration();
		$parts = array();
		if (!empty($configuration['eval'])) {
			$parts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['eval']);
		}
		return in_array('required', $parts);
	}

	/**
	 * Returns an array containing the configuration of an column.
	 *
	 * @return array
	 */
	public function getField() {
		$result = NULL;
		if ($this->hasField($fieldName)) {
			$result = $this->tca['columns'][$fieldName];
		}
		return $result;
	}

	/**
	 * Tell whether the field exists or not.
	 *
	 * @return array
	 */
	public function hasField() {
		// @todo naive implementation, improve me according to the needs. Check if info is not yet in cache.
		return isset($this->tca['columns'][$fieldName]) || in_array($fieldName, array('uid'));
	}

	/**
	 * Tell whether the field does not exist.
	 *
	 * @return array
	 */
	public function hasNotField() {
		return !$this->hasField($fieldName);
	}

	/**
	 * Returns the relation type
	 *
	 * @return string
	 */
	public function relationDataType() {
		$configuration = $this->getConfiguration();
		return $configuration['foreign_table'];
	}

	/**
	 * Returns whether the field has relation (one to many, many to many)
	 *
	 * @return bool
	 */
	public function hasRelation() {
		return NULL !== $this->getForeignTable($fieldName);
	}

	/**
	 * Returns whether the field has no relation (one to many, many to many)
	 *
	 * @return bool
	 */
	public function hasNoRelation() {
		return !$this->hasRelation($fieldName);
	}

	/**
	 * Returns whether the field has relation "many" regarless of many-to-many or one-to-many.
	 *
	 * @return bool
	 */
	public function hasRelationMany() {
		$configuration = $this->getConfiguration();
		return $this->hasRelation($fieldName) && $configuration['maxitems'] > 1;
	}

	/**
	 * Returns whether the field has relation "one" regarless of one-to-many or one-to-one.
	 *
	 * @return bool
	 */
	public function hasRelationOne() {
		$configuration = $this->getConfiguration();
		return $this->hasRelation($fieldName) && $configuration['maxitems'] == 1;
	}

	/**
	 * Returns whether the field has one-to-many relation.
	 *
	 * @return bool
	 */
	public function hasRelationOneToMany() {
		$result = FALSE;

		$foreignField = $this->getForeignField($fieldName);
		if (!empty($foreignField)) {

			// Load TCA service of foreign field..
			$foreignTable = $this->getForeignTable($fieldName);
			$tcaForeignFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($foreignTable);
			$result = $this->hasRelationOne($fieldName) && $tcaForeignFieldService->hasRelationMany($foreignField);
		}
		return $result;
	}

	/**
	 * Returns whether the field has many-to-one relation.
	 *
	 * @return bool
	 */
	public function hasRelationManyToOne() {
		$result = FALSE;

		$foreignField = $this->getForeignField($fieldName);
		if (!empty($foreignField)) {

			// Load TCA service of foreign field..
			$foreignTable = $this->getForeignTable($fieldName);
			$tcaForeignFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($foreignTable);
			$result = $this->hasRelationMany($fieldName) && $tcaForeignFieldService->hasRelationOne($foreignField);
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

		$foreignField = $this->getForeignField($fieldName);
		if (!empty($foreignField)) {

			// Load TCA service of foreign field.
			$foreignTable = $this->getForeignTable($fieldName);
			$tcaForeignFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($foreignTable);
			$result = $this->hasRelationOne($fieldName) && $tcaForeignFieldService->hasRelationOne($foreignField);
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
		return $this->hasRelation($fieldName) && isset($configuration['MM']);
	}

	/**
	 * Returns whether the field has many to many relation using comma separated values (legacy).
	 *
	 * @return bool
	 */
	public function hasRelationWithCommaSeparatedValues() {
		$configuration = $this->getConfiguration();
		return $this->hasRelation($fieldName) && !isset($configuration['MM']) && !isset($configuration['foreign_field']) && $configuration['maxitems'] > 1;
	}

	/**
	 * @return array
	 */
	public function getTca() {
		return $this->tca['columns'];
	}
}

?>