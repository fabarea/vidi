<?php
namespace TYPO3\CMS\Vidi\QueryElement;

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
 * A class to handle a SQL query
 */
class Query {

	/**
	 * Constants representing a logical OR
	 */
	const LOGICAL_OR = 'OR';

	/**
	 * Constants representing a logical OR
	 */
	const LOGICAL_AND = 'AND';

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * The default object type being returned for the Media Object Factory
	 *
	 * @var string
	 */
	protected $objectType = 'TYPO3\CMS\Vidi\Domain\Model\Content';

	/**
	 * @var \TYPO3\CMS\Vidi\QueryElement\Matcher
	 */
	protected $matcher;

	/**
	 * @var \TYPO3\CMS\Vidi\QueryElement\Order
	 */
	protected $order;

	/**
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseHandle;

	/**
	 * Tell whether it is a raw result (array) or object being returned.
	 *
	 * @var bool
	 */
	protected $rawResult = FALSE;

	/**
	 * A flag indicating whether all or some enable fields should be ignored. If TRUE, all enable fields are ignored.
	 * If--in addition to this--enableFieldsToBeIgnored is set, only fields specified there are ignored. If FALSE, all
	 * enable fields are taken into account, regardless of the enableFieldsToBeIgnored setting.
	 *
	 * @var boolean
	 */
	protected $ignoreEnableFields = FALSE;

	/**
	 * @var \TYPO3\CMS\Vidi\Tca\FieldService
	 */
	protected $tcaFieldService;

	/**
	 * @var \TYPO3\CMS\Vidi\Tca\TableService
	 */
	protected $tcaTableService;

	/**
	 * @param string $dataType which corresponds to an entry of the TCA (table name).
	 *
	 * Constructor
	 */
	public function __construct($dataType = '') {
		$this->databaseHandle = $GLOBALS['TYPO3_DB'];
		$this->tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService($dataType);
		$this->tcaTableService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getTableService($dataType);
	}

	/**
	 * Render the SQL "orderBy" part.
	 *
	 * @return string
	 */
	public function renderOrder() {
		$orderBy = '';
		if (!is_null($this->order)) {
			$orderings = $this->order->getOrderings();
			$orderBy = $delimiter = '';
			foreach ($orderings as $order => $direction) {
				$orderBy .= sprintf('%s %s %s', $delimiter, $order , $direction);
				$delimiter = ',';
			}
		}
		return trim($orderBy);
	}

	/**
	 * Render the SQL "limit" part.
	 *
	 * @return string
	 */
	public function renderLimit() {
		$limit = '';
		if ($this->limit > 0) {
			$limit = $this->offset . ',' . $this->limit;
		}
		return $limit;
	}

	/**
	 * Render the SQL "where" part
	 *
	 * @return string
	 */
	public function renderClause() {

		$clause = '1=1';

		// Case for "deleted" flag
		if ($this->tcaTableService->getDeleteField()) {
			$clause = sprintf('%s AND %s = 0', $clause, $this->tcaTableService->getDeleteField());
		}
		// Case for "language" flag
		if ($this->tcaTableService->getLanguageField()) {
			$clause = sprintf('%s AND %s = 0', $clause, $this->tcaTableService->getLanguageField());
		}

		// @todo find a better way
//		/** @var $user \TYPO3\CMS\Core\Authentication\BackendUserAuthentication */
//		$user = $GLOBALS['BE_USER'];
//		$settingManagement = \TYPO3\CMS\Vidi\Utility\Setting::getInstance();
//
//		// Add segment to handle BE Permission
//		if (TYPO3_MODE == 'BE' && $settingManagement->get('permission') && !$user->isAdmin()) {
//			if (empty($user->user['usergroup'])) {
//				$user->user['usergroup'] = 0;
//			}
//			$clause .= sprintf(' AND uid IN (SELECT uid_local FROM sys_file_begroups_mm WHERE uid_foreign IN(%s))', $user->user['usergroup']);
//		}

		if (TYPO3_MODE === 'BE' && $this->ignoreEnableFields !== TRUE) {
			$clause .= \TYPO3\CMS\Backend\Utility\BackendUtility::BEenableFields($this->tableName);
		} elseif (TYPO3_MODE === 'FE' && $this->ignoreEnableFields !== TRUE) {
			$clause .= $GLOBALS['TSFE']->sys_page->enableFields($this->tableName);
		}

		if (! is_null($this->matcher)) {

			$clauseSearchTerm = $this->getClauseSearchTerm();
			$clauseManyToMany = $this->getClauseManyToMany();

			if (strlen($clauseSearchTerm) > 0 && strlen($clauseManyToMany) > 0) {
				$queryPart = ' AND (%s) AND (%s)';
				if ($this->matcher->getDefaultLogicalOperator() === self::LOGICAL_OR) {
					$queryPart = ' AND (%s OR %s)';
				}
				$clause .= sprintf($queryPart, $clauseSearchTerm, $clauseManyToMany);
			} elseif (strlen($clauseSearchTerm) > 0) {
				$clause .= sprintf(' AND (%s)', $clauseSearchTerm);
			} elseif (strlen($clauseManyToMany) > 0) {
				$clause .= sprintf(' AND (%s)', $clauseManyToMany);
			}

			// @todo improve me. Was implemented as a hot fix. This is error prone.
			$clauseOneToMany = $this->getClauseOneToMany();
			if ($clauseOneToMany) {
				$clause .= sprintf(' AND %s', $clauseOneToMany);
			}

			$clause .= $this->getClauseMain();
		}

		return $clause;
	}

	/**
	 * Get the category clause
	 *
	 * @return string
	 */
	protected function getClauseManyToMany() {
		$clause = '';

		foreach ($this->matcher->getMatches() as $field => $values) {

			if ($this->tcaFieldService->hasRelationManyToMany($field)) {

				$tcaConfiguration = $this->tcaFieldService->getConfiguration($field);

				// First check if any it is of type string and try to retrieve a corresponding uid
				$_items = array();
				foreach ($values as $item) {
					if (is_object($item) && method_exists($item, 'getUid')) {
						$item = $item->getUid();
					}

					// TRUE means this is a character chain given.
					// So, try to be smart by checking if the string correspond to a uid in $tca_configuration['foreign_table'].
					if (!is_numeric($item)) {
						$escapedValue = $this->databaseHandle->escapeStrForLike($item, $tcaConfiguration['foreign_table']);
						$_clause = sprintf('%s LIKE "%%%s%%"',
							\TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getTableService($tcaConfiguration['foreign_table'])->getLabelField(),
							$escapedValue
						);

						$records = $this->databaseHandle->exec_SELECTgetRows('uid', $tcaConfiguration['foreign_table'], $_clause);
						if (!empty($records)) {
							foreach ($records as $record) {
								$_items[] = $record['uid'];
							}
						}
					} else {
						$_items[] = $item;
					}
				}

				if (! empty($_items)) {

					$template = <<<EOF
	uid IN (
		SELECT
			uid_foreign
		FROM
			%s
		WHERE
			tablenames = "{$this->tableName}" AND uid_local IN (%s))
EOF;
					// Add MM search
					$clause .= sprintf($template, $tcaConfiguration['MM'], implode(',', $_items));
				}
			}
		}
		return $clause;
	}

	/**
	 * Get the category clause
	 *
	 * @return string
	 */
	protected function getClauseOneToMany() {
		$clause = array();

		foreach ($this->matcher->getMatches() as $field => $values) {

			if ($this->tcaFieldService->hasRelationOne($field)) {
				$clause[] = sprintf(' %s IN (%s)', $field, $values);
			}
		}

		$result = '';
		if (!empty($clause)) {
			$result = implode('AND', $clause);
		};
		return $result;
	}

	/**
	 * Get the search term clause
	 *
	 * @return string
	 */
	protected function getClauseMain() {
		$clause = '';
		// Add constraints to the request
		// @todo Implement OR. For now only support AND. Take inspiration from logicalAnd and logicalOr.
		// @todo Add matching method $query->matching($query->equals($propertyName, $value))
		foreach ($this->matcher->getMatches() as $field => $value) {
			if ($this->tcaFieldService->hasNoRelation($field)) {
				$clause .= sprintf(' AND %s = %s',
					$field,
					$this->databaseHandle->fullQuoteStr($value, $this->tableName)
				);
			}
		}
		return $clause;
	}

	/**
	 * Get the search term clause
	 *
	 * @return string
	 */
	protected function getClauseSearchTerm() {
		$clause = '';

		if ($this->matcher->getSearchTerm()) {
			$searchTerm = $this->databaseHandle->escapeStrForLike($this->matcher->getSearchTerm(), $this->tableName);
			$searchParts = array();

			$fields = explode(',', $this->tcaTableService->getSearchableFields());

			foreach ($fields as $field) {
				$fieldType = $this->tcaFieldService->getFieldType($field);
				if ($fieldType == 'text' OR $fieldType == 'input') {
					$searchParts[] = sprintf('%s LIKE "%%%s%%"', $field, $searchTerm);
				}
			}
			$searchParts[] = sprintf('uid = "%s"', $searchTerm);
			$clause = implode(' OR ', $searchParts);
		}
		return $clause;
	}

	/**
	 * Build the query and return its result
	 *
	 * @return string the query
	 */
	public function getQuery() {
		$clause = $this->renderClause();
		$orderBy = $this->renderOrder();
		$limit = $this->renderLimit();

		return $this->databaseHandle->SELECTquery('*', $this->tableName, $clause, $groupBy = '', $orderBy, $limit);
	}

	/**
	 * Execute a query and return its result set.
	 *
	 * @return mixed
	 */
	public function execute() {
		$resource = $this->databaseHandle->sql_query($this->getQuery());
		$items = array();
		while ($row = $this->databaseHandle->sql_fetch_assoc($resource)) {

			// Get record overlay if needed
			if (TYPO3_MODE == 'FE' && $GLOBALS['TSFE']->sys_language_uid > 0) {

				$overlay = \TYPO3\CMS\Vidi\Utility\Overlays::getOverlayRecords($this->tableName, array($row['uid']), $GLOBALS['TSFE']->sys_language_uid);
				if (!empty($overlay[$row['uid']])) {
					$key = key($overlay[$row['uid']]);
					$row = $overlay[$row['uid']][$key];
				}
			}

			if (!$this->rawResult) {
				$row = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->objectType, $this->tableName, $row);
			}
			$items[] = $row;
		}
		return $items;
	}

	/**
	 * Execute a query and count its items.
	 *
	 * @return int
	 */
	public function count() {
		$clause = $this->renderClause();
		return $this->databaseHandle->exec_SELECTcountRows('*', $this->tableName, $clause);
	}

	/**
	 * @return \TYPO3\CMS\Vidi\QueryElement\Matcher
	 */
	public function getMatcher() {
		return $this->matcher;
	}

	/**
	 * @param \TYPO3\CMS\Vidi\QueryElement\Matcher $matcher
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 */
	public function setMatcher(\TYPO3\CMS\Vidi\QueryElement\Matcher $matcher) {
		$this->matcher = $matcher;
		return $this;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\QueryElement\Matcher
	 * @deprecated will be removed in Media 1.2
	 */
	public function getMatch() {
		return $this->getMatcher();
	}

	/**
	 * @param \TYPO3\CMS\Vidi\QueryElement\Match $match
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 * @deprecated will be removed in Media 1.2
	 */
	public function setMatch(\TYPO3\CMS\Vidi\QueryElement\Match $match) {
		return $this->setMatcher($match);
	}

	/**
	 * @return \TYPO3\CMS\Vidi\QueryElement\Order
	 */
	public function getOrder() {
		return $this->order;
	}

	/**
	 * @param \TYPO3\CMS\Vidi\QueryElement\Order $order
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 */
	public function setOrder(\TYPO3\CMS\Vidi\QueryElement\Order $order) {
		$this->order = $order;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * @param int $offset
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 */
	public function setOffset($offset) {
		$this->offset = (integer) $offset;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @param int $limit
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 */
	public function setLimit($limit) {
		$this->limit = (integer) $limit;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRawResult() {
		return $this->rawResult;
	}

	/**
	 * @param boolean $rawResult
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 */
	public function setRawResult($rawResult) {
		$this->rawResult = $rawResult;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getObjectType() {
		return $this->objectType;
	}

	/**
	 * @param boolean $objectType
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 */
	public function setObjectType($objectType) {
		$this->objectType = $objectType;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getIgnoreEnableFields() {
		return $this->ignoreEnableFields;
	}

	/**
	 * @param boolean $ignoreEnableFields
	 * @return \TYPO3\CMS\Vidi\QueryElement\Query
	 */
	public function setIgnoreEnableFields($ignoreEnableFields) {
		$this->ignoreEnableFields = $ignoreEnableFields;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->tableName;
	}

	/**
	 * @param string $dataType
	 * @return $this
	 */
	public function setDataType($dataType) {
		$this->tableName = $dataType;
		return $this;
	}
}

?>
