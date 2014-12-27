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
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException;

/**
 * A class to handle TCA ctrl.
 */
class TableService implements TcaServiceInterface {

	/**
	 * @var array
	 */
	protected $tca;

	/**
	 * @var array
	 */
	protected $columnTca;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * @var array
	 */
	protected $instances;

	/**
	 * @throws InvalidKeyInArrayException
	 * @param string $tableName
	 * @return \TYPO3\CMS\Vidi\Tca\TableService
	 */
	public function __construct($tableName) {
		$this->tableName = $tableName;
		if (empty($GLOBALS['TCA'][$this->tableName])) {
			throw new InvalidKeyInArrayException(sprintf('No TCA existence for table "%s"', $this->tableName), 1356945106);
		}
		$this->tca = $GLOBALS['TCA'][$this->tableName]['ctrl'];
		$this->columnTca = $GLOBALS['TCA'][$this->tableName]['columns'];
	}

	/**
	 * Tell whether the table has a label field.
	 *
	 * @throws \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException
	 * @return string
	 */
	public function hasLabelField() {
		return $this->has('label');
	}

	/**
	 * Get the label name of table name.
	 *
	 * @throws \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException
	 * @return string
	 */
	public function getLabelField() {
		$labelField = $this->get('label');
		if (empty($labelField)) {
			throw new InvalidKeyInArrayException(sprintf('No label configured for table "%s"', $this->tableName), 1385586726);
		}
		return $labelField;
	}

	/**
	 * Returns the translated label of the table name.
	 *
	 * @return string
	 */
	public function getLabel() {
		$result = LocalizationUtility::translate($this->getLabelField(), '');
		if (!$result) {
			$result = $this->getLabelField();
		}
		return $result;
	}

	/**
	 * Returns the title of the table.
	 *
	 * @return string
	 */
	public function getTitle() {
		$result = LocalizationUtility::translate($this->get('title'), '');
		if (!$result) {
			$result = $this->get('title');
		}
		return $result;
	}

	/**
	 * Return the "disabled" field.
	 *
	 * @throws \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException
	 * @return string|NULL
	 */
	public function getHiddenField() {
		$hiddenField = NULL;
		$enableColumns = $this->get('enablecolumns');
		if (is_array($enableColumns) && !empty($enableColumns['disabled'])) {
			$hiddenField = $enableColumns['disabled'];
		}
		return $hiddenField;
	}

	/**
	 * Return the "starttime" field.
	 *
	 * @throws \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException
	 * @return string|NULL
	 */
	public function getStartTimeField() {
		$startTimeField = NULL;
		$enableColumns = $this->get('enablecolumns');
		if (is_array($enableColumns) && !empty($enableColumns['starttime'])) {
			$startTimeField = $enableColumns['starttime'];
		}
		return $startTimeField;
	}

	/**
	 * Return the "endtime" field.
	 *
	 * @throws \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException
	 * @return string|NULL
	 */
	public function getEndTimeField() {
		$endTimeField = NULL;
		$enableColumns = $this->get('enablecolumns');
		if (is_array($enableColumns) && !empty($enableColumns['endtime'])) {
			$endTimeField = $enableColumns['endtime'];
		}
		return $endTimeField;
	}

	/**
	 * Tells whether the table is hidden.
	 *
	 * @return bool
	 */
	public function isHidden() {
		return isset($this->tca['hideTable']) ? $this->tca['hideTable'] : FALSE;
	}

	/**
	 * Tells whether the table is not hidden.
	 *
	 * @return bool
	 */
	public function isNotHidden() {
		return !$this->isHidden();
	}

	/**
	 * Get the "deleted" field for the table.
	 *
	 * @return string|NULL
	 */
	public function getDeletedField() {
		return $this->get('delete');
	}

	/**
	 * Get the modification time stamp field.
	 *
	 * @return string|NULL
	 */
	public function getTimeModificationField() {
		return $this->get('tstamp');
	}

	/**
	 * Get the creation time stamp field.
	 *
	 * @return string|NULL
	 */
	public function getTimeCreationField() {
		return $this->get('crdate');
	}

	/**
	 * Get the language field for the table.
	 *
	 * @return string|NULL
	 */
	public function getLanguageField() {
		return $this->get('languageField');
	}

	/**
	 * Get the field which points to the parent.
	 *
	 * @return string|NULL
	 */
	public function getLanguageParentField() {
		return $this->get('transOrigPointerField');
	}

	/**
	 * Returns the default order in the form of a SQL segment.
	 *
	 * @return string|NULL
	 */
	public function getDefaultOrderSql() {
		return $this->get('default_sortby');
	}

	/**
	 * Returns the parsed default orderings.
	 * Returns array looks like array('title' => 'ASC');
	 *
	 * @return array
	 */
	public function getDefaultOrderings() {

		// first clean up the sql segment
		$defaultOrder = str_replace('ORDER BY', '', $this->getDefaultOrderSql());
		$defaultOrderParts = GeneralUtility::trimExplode(',', $defaultOrder, TRUE);

		$orderings = array();
		foreach ($defaultOrderParts as $defaultOrderPart) {
			$parts = GeneralUtility::trimExplode(' ', $defaultOrderPart);
			if (empty($parts[1])) {
				$parts[1] = QueryInterface::ORDER_DESCENDING;
			}
			$orderings[$parts[0]] = $parts[1];
		}

		return $orderings;
	}

	/**
	 * Returns the searchable fields.
	 *
	 * @return string|NULL
	 */
	public function getSearchFields() {
		return $this->get('searchFields');
	}

	/**
	 * Tells whether the field is considered as system, e.g. uid, crdate, tstamp, etc...
	 *
	 * @param string $fieldName
	 * @return bool
	 * @deprecated use TcaService::table($tableName)->field($fieldName)->isSystem()
	 */
	public function isSystem($fieldName) {
		$systemFields = array(
			'uid', 'tstamp', 'crdate', 'deleted', 'hidden', 'starttime', 'endtime',
			'sys_language_uid', 'l18n_parent', 'l18n_diffsource',
			't3ver_oid', 't3ver_id', 't3ver_wsid', 't3ver_label', 't3ver_state', 't3ver_stage', 't3ver_count', 't3ver_tstamp', 't3_origuid'
		);
		return in_array($fieldName, $systemFields);
	}

	/**
	 * Returns an array containing the field names.
	 *
	 * @return array
	 */
	public function getFields() {
		return array_keys($this->columnTca);
	}

	/**
	 * Returns an array containing the fields and their configuration.
	 *
	 * @return array
	 */
	public function getFieldsAndConfiguration() {
		return $this->columnTca;
	}

	/**
	 * Tell whether the field exists or not.
	 *
	 * @param string $fieldName
	 * @return array
	 */
	public function hasField($fieldName) {
		if ($this->isComposite($fieldName)) {
			$parts = explode('.', $fieldName);
			$strippedFieldName = $parts[0];
			$tableName = $parts[1];

			$hasField = $this->columnTca[$strippedFieldName] && isset($GLOBALS['TCA'][$tableName]);

			// Continue checking that the $strippedFieldName is of type "group"
			if (isset($GLOBALS['TCA'][$this->tableName]['columns'][$strippedFieldName])) {
				$hasField = TcaService::table($this->tableName)->field($strippedFieldName)->isGroup(); // Group
			}
		} else {
			$hasField = isset($this->columnTca[$fieldName]) || in_array($fieldName, TcaService::getSystemFields());
		}
		return $hasField;
	}

	/**
	 * Tell whether the field name contains a path, e.g. metadata.title
	 *
	 * @param string $fieldName
	 * @return boolean
	 */
	public function isComposite($fieldName) {
		return strpos($fieldName, '.') > 0;
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
	 * Tells whether the $key exists.
	 *
	 * @param string $key
	 * @return string
	 */
	public function has($key) {
		return isset($this->tca[$key]);
	}

	/**
	 * Tells whether the table name has "workspace" support.
	 *
	 * @return string
	 */
	public function hasWorkspaceSupport() {
		return isset($this->tca['versioningWS']);
	}

	/**
	 * Tells whether the table name has "language" support.
	 *
	 * @return string
	 */
	public function hasLanguageSupport() {
		return isset($this->tca['languageField']);
	}

	/**
	 * Return configuration value given a key.
	 *
	 * @param string $key
	 * @return string|NULL
	 */
	public function get($key) {
		return $this->has($key) ? $this->tca[$key] : NULL;
	}

	/**
	 * @return array
	 */
	public function getTca() {
		return $this->tca;
	}

	/**
	 * @param string $fieldName
	 * @throws \Exception
	 * @return \TYPO3\CMS\Vidi\Tca\FieldService
	 */
	public function field($fieldName) {

		// In case field contains items.tx_table for field type "group"
		$compositeField = '';
		if (strpos($fieldName, '.') !== FALSE) {
			$compositeField = $fieldName;
			$fieldParts = explode('.', $compositeField, 2);
			$fieldName = $fieldParts[0];

			// Special when field has been instantiated without the field name and path.
			if (!empty($this->instances[$fieldName])) {
				/** @var FieldService $field */
				$field = $this->instances[$fieldName];
				$field->setCompositeField($compositeField);
			}
		}

		// True for system fields such as uid, pid that don't necessarily have a TCA.
		if (empty($this->columnTca[$fieldName]) && in_array($fieldName, TcaService::getSystemFields())) {
			$this->columnTca[$fieldName] = array();
		} elseif (empty($this->columnTca[$fieldName])) {
			$message = sprintf(
				'Does the field really exist? No TCA entry found for field "%s" for table "%s"',
				$fieldName,
				$this->tableName
			);
			throw new \Exception($message, 1385554481);
		}


		if (empty($this->instances[$fieldName])) {

			$instance = GeneralUtility::makeInstance(
				'TYPO3\CMS\Vidi\Tca\FieldService',
				$fieldName,
				$this->columnTca[$fieldName],
				$this->tableName,
				$compositeField
			);

			$this->instances[$fieldName] = $instance;
		}
		return $this->instances[$fieldName];
	}

}
