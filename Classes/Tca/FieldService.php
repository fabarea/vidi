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
use TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException;

/**
 * A class to handle TCA field configuration
 * @deprecated Use the Column Service instead.
 */
class FieldService implements \TYPO3\CMS\Vidi\Tca\TcaServiceInterface {

	/**
	 * @var array
	 */
	protected $tca;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @throws InvalidKeyInArrayException
	 * @param string $tableName
	 * @return \TYPO3\CMS\Vidi\Tca\FieldService
	 */
	public function __construct($tableName) {
		$this->tableName = $tableName;
		if (empty($GLOBALS['TCA'][$this->tableName])) {
			throw new InvalidKeyInArrayException('No TCA existence for table name: ' . $this->tableName, 1356945107);
		}
		$this->tca = $GLOBALS['TCA'][$this->tableName];
	}

	/**
	 * Returns an array containing column names
	 *
	 * @return array
	 * @deprecated
	 */
	public function getFields() {
		return $this->tca['columns'];
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
	 * Returns the configuration for a $field
	 *
	 * @param string $fieldName
	 * @throws \Exception
	 * @return array
	 */
	public function getConfiguration($fieldName) {

		// In case field contains items.tx_table for field type "group"
		if (strpos($fieldName, '.') !== FALSE) {
			$fieldParts = explode('.', $fieldName, 2);
			$fieldName = $fieldParts[0];
		}

		$fields = $this->getFields();

		if (empty($fields[$fieldName])) {
			throw new \Exception(sprintf('No field "%s" was found in "%s".', $fieldName, $this->tableName), 1385408685);
		}
		if (empty($fields[$fieldName]['config'])) {
			throw new \Exception(sprintf('No configuration available for field "%s".', $fieldName, $this->tableName), 1385408686);
		}
		return $fields[$fieldName]['config'];
	}

	/**
	 * Returns the foreign field of a given field (opposite relational field).
	 * If no relation exists, returns NULL.
	 *
	 * @param string $fieldName
	 * @return string|NULL
	 */
	public function getForeignField($fieldName) {
		$result = NULL;
		$configuration = $this->getConfiguration($fieldName);

		if (!empty($configuration['foreign_field'])) {
			$result = $configuration['foreign_field'];
		} elseif ($this->hasRelationManyToMany($fieldName)) {

			$foreignTable = $this->getForeignTable($fieldName);
			$manyToManyTable = $this->getManyToManyTable($fieldName);

			// Load TCA service of foreign field.
			$tcaForeignFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($foreignTable);

			// Look into the MM relations checking for the opposite field
			foreach ($tcaForeignFieldService->getFieldNames() as $fieldName) {
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
	 * @param string $fieldName
	 * @return string|NULL
	 */
	public function getForeignTable($fieldName) {
		$result = NULL;
		$configuration = $this->getConfiguration($fieldName);

		if (!empty($configuration['foreign_table'])) {
			$result = $configuration['foreign_table'];
		} elseif ($this->isGroup($fieldName)) {
			$fieldParts = explode('.', $fieldName, 2);
			$result = $fieldParts[1];
		}
		return $result;
	}

	/**
	 * Returns the MM table of a field.
	 * If no relation exists, returns NULL.
	 *
	 * @param string $fieldName
	 * @return string|NULL
	 */
	public function getManyToManyTable($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
		return empty($configuration['MM']) ? NULL : $configuration['MM'];
	}

	/**
	 * Returns the a possible additional table name used in MM relations.
	 * If no table name exists, returns NULL.
	 *
	 * @param string $fieldName
	 * @return string|NULL
	 */
	public function getAdditionalTableNameCondition($fieldName) {
		$result = NULL;
		$configuration = $this->getConfiguration($fieldName);

		if (!empty($configuration['MM_match_fields']['tablenames'])) {
			$result = $configuration['MM_match_fields']['tablenames'];
		} elseif ($this->isGroup($fieldName)) {
			$fieldParts = explode('.', $fieldName, 2);
			$result = $fieldParts[1];
		}

		return $result;
	}

	/**
	 * Returns whether the field name is the opposite in MM relation.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function isOppositeRelation($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
		return isset($configuration['MM_opposite_field']);
	}

	/**
	 * Returns the configuration for a $field.
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public function getFieldType($fieldName) {
		if (is_int(strpos($fieldName, '--palette--'))) {
			return 'palette';
		}
		if (is_int(strpos($fieldName, '--widget--'))) {
			return 'widget';
		}
		$configuration = $this->getConfiguration($fieldName);
		$result = $configuration['type'];

		if (!empty($configuration['eval'])) {
			$parts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['eval']);
			if (in_array('datetime', $parts)) {
				$result = 'datetime';
			}
			if (in_array('date', $parts)) {
				$result = 'date';
			}
		}
		return $result;
	}

	/**
	 * Get the translation of a label given a column.
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public function getLabel($fieldName) {
		$result = '';
		if ($this->hasLabel($fieldName)) {
			$field = $this->getField($fieldName);
			$result = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($field['label'], '');
		}
		return $result;
	}

	/**
	 * Get the translation of a label given a column.
	 *
	 * @param string $fieldName
	 * @param string $itemValue the item value to search for.
	 * @return string
	 */
	public function getLabelForItem($fieldName, $itemValue) {
		$result = '';
		$configuration = $this->getConfiguration($fieldName);
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
	 * @param string $fieldName
	 * @param string $itemValue the item value to search for.
	 * @return string
	 */
	public function getIconForItem($fieldName, $itemValue) {
		$result = '';
		$configuration = $this->getConfiguration($fieldName);
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
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasLabel($fieldName) {
		$field = $this->getField($fieldName);
		return empty($field['label']) ? FALSE : TRUE;
	}

	/**
	 * Returns whether the field is numerical.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function isNumerical($fieldName) {
		$result = in_array($fieldName, array('uid', 'pid'));
		if ($result === FALSE) {
			$configuration = $this->getConfiguration($fieldName);
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
	 * @param string $fieldName
	 * @return bool
	 */
	public function isTextArea($fieldName) {
		$type = $this->getFieldType($fieldName);
		return $type === 'text';
	}

	/**
	 * Returns whether the field is of type select.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function isSelect($fieldName) {
		$type = $this->getFieldType($fieldName);
		return $type === 'select';
	}

	/**
	 * Returns whether the field is of type db.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function isGroup($fieldName) {
		$type = $this->getFieldType($fieldName);
		return $type === 'group';
	}

	/**
	 * Returns whether the field is required.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function isRequired($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
		$parts = array();
		if (!empty($configuration['eval'])) {
			$parts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $configuration['eval']);
		}
		return in_array('required', $parts);
	}

	/**
	 * Returns an array containing the configuration of an column.
	 *
	 * @param string $fieldName
	 * @return array
	 */
	public function getField($fieldName) {
		$result = NULL;
		if ($this->hasField($fieldName)) {
			$result = $this->tca['columns'][$fieldName];
		}
		return $result;
	}

	/**
	 * Tell whether the field exists or not.
	 *
	 * @param string $fieldName
	 * @return array
	 */
	public function hasField($fieldName) {
		// @todo naive implementation, improve me according to the needs. Check if info is not yet in cache.
		return isset($this->tca['columns'][$fieldName]) || in_array($fieldName, array('uid'));
	}

	/**
	 * Tell whether the field does not exist.
	 *
	 * @param string $fieldName
	 * @return array
	 */
	public function hasNotField($fieldName) {
		return !$this->hasField($fieldName);
	}

	/**
	 * Returns the relation type
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public function relationDataType($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
		return $configuration['foreign_table'];
	}

	/**
	 * Returns whether the field has relation (one to many, many to many)
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelation($fieldName) {
		return NULL !== $this->getForeignTable($fieldName);
	}

	/**
	 * Returns whether the field has no relation (one to many, many to many)
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasNoRelation($fieldName) {
		return !$this->hasRelation($fieldName);
	}

	/**
	 * Returns whether the field has relation "many" regarless of many-to-many or one-to-many.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelationMany($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
		return $this->hasRelation($fieldName) && $configuration['maxitems'] > 1;
	}

	/**
	 * Returns whether the field has relation "one" regarless of one-to-many or one-to-one.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelationOne($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
		return $this->hasRelation($fieldName) && $configuration['maxitems'] == 1;
	}

	/**
	 * Returns whether the field has one-to-many relation.
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelationOneToMany($fieldName) {
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
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelationManyToOne($fieldName) {
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
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelationOneToOne($fieldName) {
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
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelationManyToMany($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
		return $this->hasRelation($fieldName) && isset($configuration['MM']);
	}

	/**
	 * Returns whether the field has many to many relation using comma separated values (legacy).
	 *
	 * @param string $fieldName
	 * @return bool
	 */
	public function hasRelationWithCommaSeparatedValues($fieldName) {
		$configuration = $this->getConfiguration($fieldName);
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