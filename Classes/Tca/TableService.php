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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException;

/**
 * A class to handle TCA ctrl.
 */
class TableService implements \TYPO3\CMS\Vidi\Tca\TcaServiceInterface {

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
	 * Get the delete field for the table.
	 *
	 * @return string
	 */
	public function getDeleteField() {
		return $this->get('delete');
	}

	/**
	 * Get the modification time stamp field.
	 *
	 * @return string
	 */
	public function getTimeModificationField() {
		return $this->get('tstamp');
	}

	/**
	 * Get the creation time stamp field.
	 *
	 * @return string
	 */
	public function getTimeCreationField() {
		return $this->get('crdate');
	}

	/**
	 * Get the language field for the table.
	 *
	 * @return string
	 */
	public function getLanguageField() {
		return $this->get('languageField');
	}

	/**
	 * Returns the default order in the form of a SQL segment.
	 *
	 * @return string
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
	 * @return string
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
		return isset($this->columnTca[$fieldName]) || in_array($fieldName, TcaService::getSystemFields());
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
	 * Return configuration value given a key.
	 *
	 * @param string $key
	 * @return string
	 */
	public function get($key) {
		$result = NULL;
		if (isset($this->tca[$key])) {
			$result = $this->tca[$key];
		}
		return $result;
	}

	/**
	 * @return array
	 */
	public function getTca() {
		return $this->tca;
	}

	/**
	 * @var array
	 */
	protected $instances;

	/**
	 * @param string $fieldName
	 * @throws \Exception
	 * @return \TYPO3\CMS\Vidi\Tca\ColumnService
	 */
	public function field($fieldName) {

		// In case field contains items.tx_table for field type "group"
		$fieldNameAndPath = '';
		if (strpos($fieldName, '.') !== FALSE) {
			$fieldNameAndPath = $fieldName;
			$fieldParts = explode('.', $fieldNameAndPath, 2);
			$fieldName = $fieldParts[0];

			// Special when field has been instantiated without the field name and path.
			if (!empty($this->instances[$fieldName])) {
				/** @var ColumnService $fieldTcaService */
				$fieldTcaService = $this->instances[$fieldName];
				$fieldTcaService->setFieldNameAndPath($fieldNameAndPath);
			}
		}

		// True for system fields such as uid, pid that don't necessarily have a TCA.
		if (empty($this->columnTca[$fieldName]) && in_array($fieldName, TcaService::getSystemFields())) {
			$this->columnTca[$fieldName] = array();
		} elseif (empty($this->columnTca[$fieldName])) {

			throw new \Exception(sprintf('Does the field really exist? No TCA entry found for field "%s"', $fieldName), 1385554481);
		}


		if (empty($this->instances[$fieldName])) {
			$className = 'TYPO3\CMS\Vidi\Tca\ColumnService';
			$instance = GeneralUtility::makeInstance($className, $fieldName, $this->columnTca[$fieldName], $this->tableName, $fieldNameAndPath);
			$this->instances[$fieldName] = $instance;
		}
		return $this->instances[$fieldName];
	}
}
