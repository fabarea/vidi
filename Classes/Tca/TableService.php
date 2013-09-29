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
 * A class to handle TCA ctrl.
 */
class TableService implements \TYPO3\CMS\Vidi\Tca\TcaServiceInterface {

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
	 * @throws \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException
	 * @param string $tableName
	 * @return \TYPO3\CMS\Vidi\Tca\TableService
	 */
	public function __construct($tableName) {
		$this->tableName = $tableName;
		if (empty($GLOBALS['TCA'][$this->tableName])) {
			throw new \TYPO3\CMS\Vidi\Exception\InvalidKeyInArrayException('No TCA existence for table name: ' . $this->tableName, 1356945106);
		}
		$this->tca = $GLOBALS['TCA'][$this->tableName]['ctrl'];
	}

	/**
	 * Get the label name of table name.
	 *
	 * @return string
	 */
	public function getLabelField() {
		return $this->get('label');
	}

	/**
	 * Returns the translated label of the table name.
	 *
	 * @return string
	 */
	public function getLabel() {
		$result = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->getLabelField(), '');
		if (! $result) {
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
		$result = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($this->get('title'), '');
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
	 */
	public function isSystem($fieldName) {
		$systemFields = array('uid', 'tstamp', 'crdate', 'sys_language_uid');
		return in_array($fieldName, $systemFields);
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
}
?>