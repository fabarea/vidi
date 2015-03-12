<?php
namespace TYPO3\CMS\Vidi\Persistence\Storage;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\DynamicOperandInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\JoinInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LowerCaseInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValueInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\UpperCaseInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * A Storage backend
 */
class VidiDbBackend {

	const OPERATOR_EQUAL_TO_NULL = 'operatorEqualToNull';
	const OPERATOR_NOT_EQUAL_TO_NULL = 'operatorNotEqualToNull';

	/**
	 * The TYPO3 database object
	 *
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $databaseHandle;

	/**
	 * The TYPO3 page repository. Used for language and workspace overlay
	 *
	 * @var PageRepository
	 */
	protected $pageRepository;

	/**
	 * A first-level TypoScript configuration cache
	 *
	 * @var array
	 */
	protected $pageTSConfigCache = array();

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\CacheService
	 * @inject
	 */
	protected $cacheService;

	/**
	 * @var \TYPO3\CMS\Core\Cache\CacheManager
	 * @inject
	 */
	protected $cacheManager;

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
	 */
	protected $tableColumnCache;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\EnvironmentService
	 * @inject
	 */
	protected $environmentService;

	/**
	 * @var \TYPO3\CMS\Vidi\Persistence\Query
	 */
	protected $query;

	/**
	 * Store some info related to table name and its aliases.
	 *
	 * @var array
	 */
	protected $tableNameAliases = array(
		'aliases' => array(),
		'aliasIncrement' => array(),
	);

	/**
	 * Use to store the current foreign table name alias.
	 *
	 * @var string
	 */
	protected $currentChildTableNameAlias = '';

	/**
	 * The default object type being returned for the Media Object Factory
	 *
	 * @var string
	 */
	protected $objectType = 'TYPO3\CMS\Vidi\Domain\Model\Content';

	/**
	 * Constructor. takes the database handle from $GLOBALS['TYPO3_DB']
	 */
	public function __construct(QueryInterface $query) {
		$this->query = $query;
		$this->databaseHandle = $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Lifecycle method
	 *
	 * @return void
	 */
	public function initializeObject() {
		$this->tableColumnCache = $this->cacheManager->getCache('extbase_typo3dbbackend_tablecolumns');
	}

	/**
	 * @param array $identifier
	 * @return string
	 */
	protected function parseIdentifier(array $identifier) {
		$fieldNames = array_keys($identifier);
		$suffixedFieldNames = array();
		foreach ($fieldNames as $fieldName) {
			$suffixedFieldNames[] = $fieldName . '=?';
		}
		return implode(' AND ', $suffixedFieldNames);
	}

	/**
	 * Returns the result of the query
	 */
	public function fetchResult() {

		$parameters = array();
		$statementParts = $this->parseQuery($this->query, $parameters);
		$statementParts = $this->processStatementStructureForRecursiveMMRelation($statementParts); // Mmm... check if that is the right way of doing that.

		$sql = $this->buildQuery($statementParts);
		$tableName = '';
		if (is_array($statementParts) && !empty($statementParts['tables'][0])) {
			$tableName = $statementParts['tables'][0];
		}
		$this->replacePlaceholders($sql, $parameters, $tableName);
		#print $sql; exit(); // @debug

		$result = $this->databaseHandle->sql_query($sql);
		$this->checkSqlErrors($sql);
		$rows = $this->getRowsFromResult($result);
		$this->databaseHandle->sql_free_result($result);

		return $rows;
	}

	/**
	 * Returns the number of tuples matching the query.
	 *
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\BadConstraintException
	 * @return int The number of matching tuples
	 */
	public function countResult() {

		$parameters = array();
		$statementParts = $this->parseQuery($this->query, $parameters);
		$statementParts = $this->processStatementStructureForRecursiveMMRelation($statementParts); // Mmm... check if that is the right way of doing that.
		// Reset $statementParts for valid table return
		reset($statementParts);

		// if limit is set, we need to count the rows "manually" as COUNT(*) ignores LIMIT constraints
		if (!empty($statementParts['limit'])) {
			$statement = $this->buildQuery($statementParts);
			$this->replacePlaceholders($statement, $parameters, current($statementParts['tables']));
			#print $statement; exit(); // @debug
			$result = $this->databaseHandle->sql_query($statement);
			$this->checkSqlErrors($statement);
			$count = $this->databaseHandle->sql_num_rows($result);
		} else {
			$statementParts['fields'] = array('COUNT(*)');
			// having orderings without grouping is not compatible with non-MySQL DBMS
			$statementParts['orderings'] = array();
			if (isset($statementParts['keywords']['distinct'])) {
				unset($statementParts['keywords']['distinct']);
				$distinctField = $this->query->getDistinct() ? $this->query->getDistinct() : 'uid';
				$statementParts['fields'] = array('COUNT(DISTINCT ' . reset($statementParts['tables']) . '.' . $distinctField . ')');
			}

			$statement = $this->buildQuery($statementParts);
			$this->replacePlaceholders($statement, $parameters, current($statementParts['tables']));

			#print $statement; exit(); // @debug
			$result = $this->databaseHandle->sql_query($statement);
			$this->checkSqlErrors($statement);
			$count = 0;
			if ($result) {
				$row = $this->databaseHandle->sql_fetch_assoc($result);
				$count = current($row);
			}
		}
		$this->databaseHandle->sql_free_result($result);
		return (int)$count;
	}

	/**
	 * Parses the query and returns the SQL statement parts.
	 *
	 * @param QueryInterface $query The query
	 * @param array &$parameters
	 * @return array The SQL statement parts
	 */
	public function parseQuery(QueryInterface $query, array &$parameters) {
		$statementParts = array();
		$statementParts['keywords'] = array();
		$statementParts['tables'] = array();
		$statementParts['unions'] = array();
		$statementParts['fields'] = array();
		$statementParts['where'] = array();
		$statementParts['additionalWhereClause'] = array();
		$statementParts['orderings'] = array();
		$statementParts['limit'] = array();
		$source = $query->getSource();
		$this->parseSource($source, $statementParts);
		$this->parseConstraint($query->getConstraint(), $source, $statementParts, $parameters);
		$this->parseOrderings($query->getOrderings(), $source, $statementParts);
		$this->parseLimitAndOffset($query->getLimit(), $query->getOffset(), $statementParts);
		$tableNames = array_unique(array_keys($statementParts['tables'] + $statementParts['unions']));
		foreach ($tableNames as $tableNameOrAlias) {
			if (is_string($tableNameOrAlias) && strlen($tableNameOrAlias) > 0) {
				$this->addAdditionalWhereClause($query->getQuerySettings(), $tableNameOrAlias, $statementParts);
			}
		}

		return $statementParts;
	}

	/**
	 * Fiddle with the statement structure to handle recursive MM relations.
	 * For the recursive MM query to work, we must invert some values.
	 * Let see if that is the best way of doing that...
	 *
	 * @param array $statementParts
	 * @return array
	 */
	public function processStatementStructureForRecursiveMMRelation(array $statementParts) {

		if ($this->hasRecursiveMMRelation()) {
			$tableName = $this->query->getType();

			// In order the MM query to work for a recursive MM query, we must invert some values.
			// tx_domain_model_foo0 (the alias) <--> tx_domain_model_foo (the origin table name)
			$values = array();
			foreach ($statementParts['fields'] as $key => $value) {
				$values[$key] = str_replace($tableName, $tableName . '0', $value);
			}
			$statementParts['fields'] = $values;

			// Same comment as above.
			$values = array();
			foreach ($statementParts['where'] as $key => $value) {
				$values[$key] = str_replace($tableName . '0', $tableName, $value);
			}
			$statementParts['where'] = $values;

			// We must be more restrictive by transforming the "left" union by "inner"
			$values = array();
			foreach ($statementParts['unions'] as $key => $value) {
				$values[$key] = str_replace('LEFT JOIN', 'INNER JOIN', $value);
			}
			$statementParts['unions'] = $values;
		}

		return $statementParts;
	}

	/**
	 * Tell whether there is a recursive MM relation.
	 *
	 * @return bool
	 */
	public function hasRecursiveMMRelation() {
		return isset($this->tableNameAliases['aliasIncrement'][$this->query->getType()]);

	}

	/**
	 * Returns the statement, ready to be executed.
	 *
	 * @param array $statementParts The SQL statement parts
	 * @return string The SQL statement
	 */
	public function buildQuery(array $statementParts) {

		// Add more statement to the UNION part.
		if (!empty($statementParts['unions'])) {
			foreach ($statementParts['unions'] as $tableName => $unionPart) {
				if (!empty($statementParts['additionalWhereClause'][$tableName])) {
					$statementParts['unions'][$tableName] .= ' AND ' . implode(' AND ', $statementParts['additionalWhereClause'][$tableName]);
				}
			}
		}

		$statement = 'SELECT ' . implode(' ', $statementParts['keywords']) . ' ' . implode(',', $statementParts['fields']) . ' FROM ' . implode(' ', $statementParts['tables']) . ' ' . implode(' ', $statementParts['unions']);
		if (!empty($statementParts['where'])) {
			$statement .= ' WHERE ' . implode('', $statementParts['where']);
			if (!empty($statementParts['additionalWhereClause'][$this->query->getType()])) {
				$statement .= ' AND ' . implode(' AND ', $statementParts['additionalWhereClause'][$this->query->getType()]);
			}
		} elseif (!empty($statementParts['additionalWhereClause'])) {
			$statement .= ' WHERE ' . implode(' AND ', $statementParts['additionalWhereClause'][$this->query->getType()]);
		}
		if (!empty($statementParts['orderings'])) {
			$statement .= ' ORDER BY ' . implode(', ', $statementParts['orderings']);
		}
		if (!empty($statementParts['limit'])) {
			$statement .= ' LIMIT ' . $statementParts['limit'];
		}

		return $statement;
	}

	/**
	 * Transforms a Query Source into SQL and parameter arrays
	 *
	 * @param SourceInterface $source The source
	 * @param array &$sql
	 * @return void
	 */
	protected function parseSource(SourceInterface $source, array &$sql) {
		if ($source instanceof SelectorInterface) {
			$tableName = $source->getNodeTypeName();
			$sql['fields'][$tableName] = $tableName . '.*';
			$sql['tables'][$tableName] = $tableName;
			if ($this->query->getDistinct()) {
				$sql['fields'][$tableName] = $tableName . '.' . $this->query->getDistinct();
				$sql['keywords']['distinct'] = 'DISTINCT';
			}
		} elseif ($source instanceof JoinInterface) {
			$this->parseJoin($source, $sql);
		}
	}

	/**
	 * Transforms a Join into SQL and parameter arrays
	 *
	 * @param JoinInterface $join The join
	 * @param array &$sql The query parts
	 * @return void
	 */
	protected function parseJoin(JoinInterface $join, array &$sql) {
		$leftSource = $join->getLeft();
		$leftTableName = $leftSource->getSelectorName();
		// $sql['fields'][$leftTableName] = $leftTableName . '.*';
		$rightSource = $join->getRight();
		if ($rightSource instanceof JoinInterface) {
			$rightTableName = $rightSource->getLeft()->getSelectorName();
		} else {
			$rightTableName = $rightSource->getSelectorName();
			$sql['fields'][$leftTableName] = $rightTableName . '.*';
		}
		$sql['tables'][$leftTableName] = $leftTableName;
		$sql['unions'][$rightTableName] = 'LEFT JOIN ' . $rightTableName;
		$joinCondition = $join->getJoinCondition();
		if ($joinCondition instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\EquiJoinCondition) {
			$column1Name = $joinCondition->getProperty1Name();
			$column2Name = $joinCondition->getProperty2Name();
			$sql['unions'][$rightTableName] .= ' ON ' . $joinCondition->getSelector1Name() . '.' . $column1Name . ' = ' . $joinCondition->getSelector2Name() . '.' . $column2Name;
		}
		if ($rightSource instanceof JoinInterface) {
			$this->parseJoin($rightSource, $sql);
		}
	}

	/**
	 * Transforms a constraint into SQL and parameter arrays
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint The constraint
	 * @param SourceInterface $source The source
	 * @param array &$sql The query parts
	 * @param array &$parameters The parameters that will replace the markers
	 * @return void
	 */
	protected function parseConstraint(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint = NULL, SourceInterface $source, array &$sql, array &$parameters) {
		if ($constraint instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\AndInterface) {
			$sql['where'][] = '(';
			$this->parseConstraint($constraint->getConstraint1(), $source, $sql, $parameters);
			$sql['where'][] = ' AND ';
			$this->parseConstraint($constraint->getConstraint2(), $source, $sql, $parameters);
			$sql['where'][] = ')';
		} elseif ($constraint instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface) {
			$sql['where'][] = '(';
			$this->parseConstraint($constraint->getConstraint1(), $source, $sql, $parameters);
			$sql['where'][] = ' OR ';
			$this->parseConstraint($constraint->getConstraint2(), $source, $sql, $parameters);
			$sql['where'][] = ')';
		} elseif ($constraint instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\NotInterface) {
			$sql['where'][] = 'NOT (';
			$this->parseConstraint($constraint->getConstraint(), $source, $sql, $parameters);
			$sql['where'][] = ')';
		} elseif ($constraint instanceof ComparisonInterface) {
			$this->parseComparison($constraint, $source, $sql, $parameters);
		}
	}

	/**
	 * Parse a Comparison into SQL and parameter arrays.
	 *
	 * @param ComparisonInterface $comparison The comparison to parse
	 * @param SourceInterface $source The source
	 * @param array &$sql SQL query parts to add to
	 * @param array &$parameters Parameters to bind to the SQL
	 * @throws Exception\RepositoryException
	 * @return void
	 */
	protected function parseComparison(ComparisonInterface $comparison, SourceInterface $source, array &$sql, array &$parameters) {
		$operand1 = $comparison->getOperand1();
		$operator = $comparison->getOperator();
		$operand2 = $comparison->getOperand2();
		if ($operator === QueryInterface::OPERATOR_IN) {
			$items = array();
			$hasValue = FALSE;
			foreach ($operand2 as $value) {
				$value = $this->getPlainValue($value);
				if ($value !== NULL) {
					$items[] = $value;
					$hasValue = TRUE;
				}
			}
			if ($hasValue === FALSE) {
				$sql['where'][] = '1<>1';
			} else {
				$this->parseDynamicOperand($operand1, $operator, $source, $sql, $parameters, NULL);
				$parameters[] = $items;
			}
		} elseif ($operator === QueryInterface::OPERATOR_CONTAINS) {
			if ($operand2 === NULL) {
				$sql['where'][] = '1<>1';
			} else {
				throw new \Exception('Not implemented! Contact extension author.', 1412931227);
				# @todo re-implement me if necessary.
				#$tableName = $this->query->getType();
				#$propertyName = $operand1->getPropertyName();
				#while (strpos($propertyName, '.') !== FALSE) {
				#	$this->addUnionStatement($tableName, $propertyName, $sql);
				#}
				#$columnName = $propertyName;
				#$columnMap = $propertyName;
				#$typeOfRelation = $columnMap instanceof ColumnMap ? $columnMap->getTypeOfRelation() : NULL;
				#if ($typeOfRelation === ColumnMap::RELATION_HAS_AND_BELONGS_TO_MANY) {
				#	$relationTableName = $columnMap->getRelationTableName();
				#	$sql['where'][] = $tableName . '.uid IN (SELECT ' . $columnMap->getParentKeyFieldName() . ' FROM ' . $relationTableName . ' WHERE ' . $columnMap->getChildKeyFieldName() . '=?)';
				#	$parameters[] = intval($this->getPlainValue($operand2));
				#} elseif ($typeOfRelation === ColumnMap::RELATION_HAS_MANY) {
				#	$parentKeyFieldName = $columnMap->getParentKeyFieldName();
				#	if (isset($parentKeyFieldName)) {
				#		$childTableName = $columnMap->getChildTableName();
				#		$sql['where'][] = $tableName . '.uid=(SELECT ' . $childTableName . '.' . $parentKeyFieldName . ' FROM ' . $childTableName . ' WHERE ' . $childTableName . '.uid=?)';
				#		$parameters[] = intval($this->getPlainValue($operand2));
				#	} else {
				#		$sql['where'][] = 'FIND_IN_SET(?,' . $tableName . '.' . $columnName . ')';
				#		$parameters[] = intval($this->getPlainValue($operand2));
				#	}
				#} else {
				#	throw new Exception\RepositoryException('Unsupported or non-existing property name "' . $propertyName . '" used in relation matching.', 1327065745);
				#}
			}
		} else {
			if ($operand2 === NULL) {
				if ($operator === QueryInterface::OPERATOR_EQUAL_TO) {
					$operator = self::OPERATOR_EQUAL_TO_NULL;
				} elseif ($operator === QueryInterface::OPERATOR_NOT_EQUAL_TO) {
					$operator = self::OPERATOR_NOT_EQUAL_TO_NULL;
				}
			}
			$this->parseDynamicOperand($operand1, $operator, $source, $sql, $parameters);
			$parameters[] = $this->getPlainValue($operand2);
		}
	}

	/**
	 * Returns a plain value, i.e. objects are flattened out if possible.
	 *
	 * @param mixed $input
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException
	 * @return mixed
	 */
	protected function getPlainValue($input) {
		if (is_array($input)) {
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException('An array could not be converted to a plain value.', 1274799932);
		}
		if ($input instanceof \DateTime) {
			return $input->format('U');
		} elseif (is_object($input)) {
			if ($input instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy) {
				$realInput = $input->_loadRealInstance();
			} else {
				$realInput = $input;
			}
			if ($realInput instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
				return $realInput->getUid();
			} else {
				throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException('An object of class "' . get_class($realInput) . '" could not be converted to a plain value.', 1274799934);
			}
		} elseif (is_bool($input)) {
			return $input === TRUE ? 1 : 0;
		} else {
			return $input;
		}
	}

	/**
	 * Parse a DynamicOperand into SQL and parameter arrays.
	 *
	 * @param DynamicOperandInterface $operand
	 * @param string $operator One of the JCR_OPERATOR_* constants
	 * @param SourceInterface $source The source
	 * @param array &$sql The query parts
	 * @param array &$parameters The parameters that will replace the markers
	 * @param string $valueFunction an optional SQL function to apply to the operand value
	 * @return void
	 */
	protected function parseDynamicOperand(DynamicOperandInterface $operand, $operator, SourceInterface $source, array &$sql, array &$parameters, $valueFunction = NULL) {
		if ($operand instanceof LowerCaseInterface) {
			$this->parseDynamicOperand($operand->getOperand(), $operator, $source, $sql, $parameters, 'LOWER');
		} elseif ($operand instanceof UpperCaseInterface) {
			$this->parseDynamicOperand($operand->getOperand(), $operator, $source, $sql, $parameters, 'UPPER');
		} elseif ($operand instanceof PropertyValueInterface) {
			$propertyName = $operand->getPropertyName();

			// Reset value.
			$this->currentChildTableNameAlias = '';

			if ($source instanceof SelectorInterface) {
				$tableName = $this->query->getType();
				while (strpos($propertyName, '.') !== FALSE) {
					$this->addUnionStatement($tableName, $propertyName, $sql);
				}
			} elseif ($source instanceof JoinInterface) {
				$tableName = $source->getJoinCondition()->getSelector1Name();
			}

			$columnName = $propertyName;
			$operator = $this->resolveOperator($operator);
			$constraintSQL = '';
			if ($valueFunction === NULL) {
				$constraintSQL .= (!empty($tableName) ? $tableName . '.' : '') . $columnName . ' ' . $operator . ' ?';
			} else {
				$constraintSQL .= $valueFunction . '(' . (!empty($tableName) ? $tableName . '.' : '') . $columnName . ') ' . $operator . ' ?';
			}

			if (isset($tableName) && !empty($this->currentChildTableNameAlias)) {
				$constraintSQL = $this->replaceTableNameByAlias($tableName, $this->currentChildTableNameAlias, $constraintSQL);
			}
			$sql['where'][] = $constraintSQL;
		}
	}

	/**
	 * @param string &$tableName
	 * @param array &$propertyPath
	 * @param array &$sql
	 * @throws Exception
	 * @throws Exception\InvalidRelationConfigurationException
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\MissingColumnMapException
	 */
	protected function addUnionStatement(&$tableName, &$propertyPath, array &$sql) {

		$table = TcaService::table($tableName);

		$explodedPropertyPath = explode('.', $propertyPath, 2);
		$fieldName = $explodedPropertyPath[0];

		// Field of type "group" are special because property path must contain the table name
		// to determine the relation type. Example for sys_category, property path will look like "items.sys_file"
		if ($table->field($fieldName)->isGroup()) {
			$parts = explode('.', $propertyPath, 3);
			$explodedPropertyPath[0] = $parts[0] . '.' . $parts[1];
			$explodedPropertyPath[1] = $parts[2];
			$fieldName = $explodedPropertyPath[0];
		}

		$parentKeyFieldName = $table->field($fieldName)->getForeignField();
		$childTableName = $table->field($fieldName)->getForeignTable();

		if ($childTableName === NULL) {
			throw new Exception\InvalidRelationConfigurationException('The relation information for property "' . $fieldName . '" of class "' . $tableName . '" is missing.', 1353170925);
		}

		if ($table->field($fieldName)->hasOne()) { // includes relation "one-to-one" and "many-to-one"
			// sometimes the opposite relation is not defined. We don't want to force this config for backward compatibility reasons.
			// $parentKeyFieldName === NULL does the trick somehow. Before condition was if (isset($parentKeyFieldName))
			if ($table->field($fieldName)->hasRelationManyToOne() || $parentKeyFieldName === NULL) {
				$sql['unions'][$childTableName] = 'LEFT JOIN ' . $childTableName . ' ON ' . $tableName . '.' . $fieldName . '=' . $childTableName . '.uid';
			} else {
				$sql['unions'][$childTableName] = 'LEFT JOIN ' . $childTableName . ' ON ' . $tableName . '.uid=' . $childTableName . '.' . $parentKeyFieldName;
			}
		} elseif ($table->field($fieldName)->hasRelationManyToMany()) {
			$relationTableName = $table->field($fieldName)->getManyToManyTable();

			$parentKeyFieldName = $table->field($fieldName)->isOppositeRelation() ? 'uid_foreign' : 'uid_local';
			$childKeyFieldName = !$table->field($fieldName)->isOppositeRelation() ? 'uid_foreign' : 'uid_local';

			// MM table e.g sys_category_record_mm
			$relationTableNameAlias = $this->generateAlias($relationTableName);
			$join = sprintf(
				'LEFT JOIN %s AS %s ON %s.uid=%s.%s', $relationTableName,
				$relationTableNameAlias,
				$tableName,
				$relationTableNameAlias,
				$parentKeyFieldName
			);
			$sql['unions'][$relationTableNameAlias] = $join;

			// Foreign table e.g sys_category
			$childTableNameAlias = $this->generateAlias($childTableName);
			$this->currentChildTableNameAlias = $childTableNameAlias;
			$join = sprintf(
				'LEFT JOIN %s AS %s ON %s.%s=%s.uid',
				$childTableName,
				$childTableNameAlias,
				$relationTableNameAlias,
				$childKeyFieldName,
				$childTableNameAlias
			);
			$sql['unions'][$childTableNameAlias] = $join;

			// Find a possible table name for a MM condition.
			$tableNameCondition = $table->field($fieldName)->getAdditionalTableNameCondition();
			if ($tableNameCondition) {

				// If we can find a source file name,  we can then retrieve more MM conditions from the TCA such as a field name.
				$sourceFileName = $this->query->getSourceFieldName();
				if (empty($sourceFileName)) {
					$additionalMMConditions = array(
						'tablenames' => $tableNameCondition,
					);
				} else {
					$additionalMMConditions = TcaService::table($tableNameCondition)->field($sourceFileName)->getAdditionalMMCondition();
				}

				foreach ($additionalMMConditions as $additionalFieldName => $additionalMMCondition) {
					$additionalJoin = sprintf(' AND %s.%s = "%s"', $relationTableNameAlias, $additionalFieldName, $additionalMMCondition);
					$sql['unions'][$relationTableNameAlias] .= $additionalJoin;

					$additionalJoin = sprintf(' AND %s.%s = "%s"', $relationTableNameAlias, $additionalFieldName, $additionalMMCondition);
					$sql['unions'][$childTableNameAlias] .= $additionalJoin;
				}

			}



		} elseif ($table->field($fieldName)->hasMany()) { // includes relations "many-to-one" and "csv" relations
			$childTableNameAlias = $this->generateAlias($childTableName);
			$this->currentChildTableNameAlias = $childTableNameAlias;

			if (isset($parentKeyFieldName)) {
				$join = sprintf(
					'LEFT JOIN %s AS %s ON %s.uid=%s.%s',
					$childTableName,
					$childTableNameAlias,
					$tableName,
					$childTableNameAlias,
					$parentKeyFieldName
				);
				$sql['unions'][$childTableNameAlias] = $join;
			} else {
				$join = sprintf(
					'LEFT JOIN %s AS %s ON (FIND_IN_SET(%s.uid, %s.%s))',
					$childTableName,
					$childTableNameAlias,
					$childTableNameAlias,
					$tableName,
					$fieldName
				);
				$sql['unions'][$childTableNameAlias] = $join;
			}
		} else {
			throw new Exception('Could not determine type of relation.', 1252502725);
		}

		// TODO check if there is another solution for this
		$sql['keywords']['distinct'] = 'DISTINCT';
		$propertyPath = $explodedPropertyPath[1];
		$tableName = $childTableName;
	}

	/**
	 * Returns the SQL operator for the given JCR operator type.
	 *
	 * @param string $operator One of the JCR_OPERATOR_* constants
	 * @throws Exception
	 * @return string an SQL operator
	 */
	protected function resolveOperator($operator) {
		switch ($operator) {
			case self::OPERATOR_EQUAL_TO_NULL:
				$operator = 'IS';
				break;
			case self::OPERATOR_NOT_EQUAL_TO_NULL:
				$operator = 'IS NOT';
				break;
			case QueryInterface::OPERATOR_IN:
				$operator = 'IN';
				break;
			case QueryInterface::OPERATOR_EQUAL_TO:
				$operator = '=';
				break;
			case QueryInterface::OPERATOR_NOT_EQUAL_TO:
				$operator = '!=';
				break;
			case QueryInterface::OPERATOR_LESS_THAN:
				$operator = '<';
				break;
			case QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO:
				$operator = '<=';
				break;
			case QueryInterface::OPERATOR_GREATER_THAN:
				$operator = '>';
				break;
			case QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO:
				$operator = '>=';
				break;
			case QueryInterface::OPERATOR_LIKE:
				$operator = 'LIKE';
				break;
			default:
				throw new Exception('Unsupported operator encountered.', 1242816073);
		}
		return $operator;
	}

	/**
	 * Replace query placeholders in a query part by the given
	 * parameters.
	 *
	 * @param string &$sqlString The query part with placeholders
	 * @param array $parameters The parameters
	 * @param string $tableName
	 *
	 * @throws Exception
	 */
	protected function replacePlaceholders(&$sqlString, array $parameters, $tableName = 'foo') {
		// TODO profile this method again
		if (substr_count($sqlString, '?') !== count($parameters)) {
			throw new Exception('The number of question marks to replace must be equal to the number of parameters.', 1242816074);
		}
		$offset = 0;
		foreach ($parameters as $parameter) {
			$markPosition = strpos($sqlString, '?', $offset);
			if ($markPosition !== FALSE) {
				if ($parameter === NULL) {
					$parameter = 'NULL';
				} elseif (is_array($parameter) || $parameter instanceof \ArrayAccess || $parameter instanceof \Traversable) {
					$items = array();
					foreach ($parameter as $item) {
						$items[] = $this->databaseHandle->fullQuoteStr($item, $tableName);
					}
					$parameter = '(' . implode(',', $items) . ')';
				} else {
					$parameter = $this->databaseHandle->fullQuoteStr($parameter, $tableName);
				}
				$sqlString = substr($sqlString, 0, $markPosition) . $parameter . substr($sqlString, ($markPosition + 1));
			}
			$offset = $markPosition + strlen($parameter);
		}
	}

	/**
	 * Adds additional WHERE statements according to the query settings.
	 *
	 * @param QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @param string $tableNameOrAlias The table name to add the additional where clause for
	 * @param array &$statementParts
	 * @return void
	 */
	protected function addAdditionalWhereClause(QuerySettingsInterface $querySettings, $tableNameOrAlias, &$statementParts) {
		$this->addVisibilityConstraintStatement($querySettings, $tableNameOrAlias, $statementParts);
		if ($querySettings->getRespectSysLanguage()) {
			$this->addSysLanguageStatement($tableNameOrAlias, $statementParts, $querySettings);
		}
		if ($querySettings->getRespectStoragePage()) {
			$this->addPageIdStatement($tableNameOrAlias, $statementParts, $querySettings->getStoragePageIds());
		}
	}

	/**
	 * Adds enableFields and deletedClause to the query if necessary
	 *
	 * @param QuerySettingsInterface $querySettings
	 * @param string $tableNameOrAlias The database table name
	 * @param array &$statementParts The query parts
	 * @return void
	 */
	protected function addVisibilityConstraintStatement(QuerySettingsInterface $querySettings, $tableNameOrAlias, array &$statementParts) {
		$statement = '';
		$tableName = $this->resolveTableNameAlias($tableNameOrAlias);
		if (is_array($GLOBALS['TCA'][$tableName]['ctrl'])) {
			$ignoreEnableFields = $querySettings->getIgnoreEnableFields();
			$enableFieldsToBeIgnored = $querySettings->getEnableFieldsToBeIgnored();
			$includeDeleted = $querySettings->getIncludeDeleted();
			if ($this->environmentService->isEnvironmentInFrontendMode()) {
				$statement .= $this->getFrontendConstraintStatement($tableNameOrAlias, $ignoreEnableFields, $enableFieldsToBeIgnored, $includeDeleted);
			} else {
				// TYPO3_MODE === 'BE'
				$statement .= $this->getBackendConstraintStatement($tableNameOrAlias, $ignoreEnableFields, $includeDeleted);
			}

			// Remove the prefixing "AND" if any.
			if (!empty($statement)) {
				$statement = strtolower(substr($statement, 1, 3)) === 'and' ? substr($statement, 5) : $statement;
				$statementParts['additionalWhereClause'][$tableNameOrAlias][] = $statement;
			}
		}
	}

	/**
	 * Returns constraint statement for frontend context
	 *
	 * @param string $tableNameOrAlias
	 * @param boolean $ignoreEnableFields A flag indicating whether the enable fields should be ignored
	 * @param array $enableFieldsToBeIgnored If $ignoreEnableFields is true, this array specifies enable fields to be ignored. If it is NULL or an empty array (default) all enable fields are ignored.
	 * @param boolean $includeDeleted A flag indicating whether deleted records should be included
	 * @return string
	 * @throws Exception\InconsistentQuerySettingsException
	 */
	protected function getFrontendConstraintStatement($tableNameOrAlias, $ignoreEnableFields, $enableFieldsToBeIgnored = array(), $includeDeleted) {
		$statement = '';
		$tableName = $this->resolveTableNameAlias($tableNameOrAlias);
		if ($ignoreEnableFields && !$includeDeleted) {
			if (count($enableFieldsToBeIgnored)) {
				// array_combine() is necessary because of the way \TYPO3\CMS\Frontend\Page\PageRepository::enableFields() is implemented
				$statement .= $this->getPageRepository()->enableFields($tableName, -1, array_combine($enableFieldsToBeIgnored, $enableFieldsToBeIgnored));
			} else {
				$statement .= $this->getPageRepository()->deleteClause($tableName);
			}
		} elseif (!$ignoreEnableFields && !$includeDeleted) {
			$statement .= $this->getPageRepository()->enableFields($tableName);
		} elseif (!$ignoreEnableFields && $includeDeleted) {
			throw new Exception\InconsistentQuerySettingsException('Query setting "ignoreEnableFields=FALSE" can not be used together with "includeDeleted=TRUE" in frontend context.', 1327678173);
		}
		return $this->replaceTableNameByAlias($tableName, $tableNameOrAlias, $statement);
	}

	/**
	 * Returns constraint statement for backend context
	 *
	 * @param string $tableNameOrAlias
	 * @param boolean $ignoreEnableFields A flag indicating whether the enable fields should be ignored
	 * @param boolean $includeDeleted A flag indicating whether deleted records should be included
	 * @return string
	 */
	protected function getBackendConstraintStatement($tableNameOrAlias, $ignoreEnableFields, $includeDeleted) {
		$tableName = $this->resolveTableNameAlias($tableNameOrAlias);
		$statement = '';
		if (!$ignoreEnableFields) {
			$statement .= BackendUtility::BEenableFields($tableName);
		}

		// If the table is found to have "workspace" support, add the corresponding fields in the statement.
		if (TcaService::table($tableName)->hasWorkspaceSupport()) {
			if ($this->getBackendUser()->workspace === 0) {
				$statement .= ' AND ' . $tableName . '.t3ver_state<=' . new VersionState(VersionState::DEFAULT_STATE);
			} else {
				// Show only records of live and of the current workspace
				// In case we are in a Versioning preview
				$statement .= ' AND (' .
					$tableName . '.t3ver_wsid=0 OR ' .
					$tableName . '.t3ver_wsid=' . (int)$this->getBackendUser()->workspace .
					')';
			}

			// Check if this segment make sense here or whether it should be in the "if" part when we have workspace = 0
			$statement .= ' AND ' . $tableName . '.pid<>-1';
		}

		if (!$includeDeleted) {
			$statement .= BackendUtility::deleteClause($tableName);
		}

		return $this->replaceTableNameByAlias($tableName, $tableNameOrAlias, $statement);
	}

	/**
	 * Builds the language field statement
	 *
	 * @param string $tableNameOrAlias The database table name
	 * @param array &$statementParts The query parts
	 * @param QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @throws Exception
	 * @return void
	 */
	protected function addSysLanguageStatement($tableNameOrAlias, array &$statementParts, $querySettings) {

		$tableName = $this->resolveTableNameAlias($tableNameOrAlias);
		if (is_array($GLOBALS['TCA'][$tableName]['ctrl'])) {
			if (!empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])) {
				// Select all entries for the current language
				$additionalWhereClause = $tableNameOrAlias . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . ' IN (' . intval($querySettings->getLanguageUid()) . ',-1)';
				// If any language is set -> get those entries which are not translated yet
				// They will be removed by t3lib_page::getRecordOverlay if not matching overlay mode
				if (isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])
					&& $querySettings->getLanguageUid() > 0
				) {
					$additionalWhereClause .= ' OR (' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . '=0' .
						' AND ' . $tableName . '.uid NOT IN (SELECT ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] .
						' FROM ' . $tableName .
						' WHERE ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] . '>0' .
						' AND ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . '>0';

					// Add delete clause to ensure all entries are loaded
					if (isset($GLOBALS['TCA'][$tableName]['ctrl']['delete'])) {
						$additionalWhereClause .= ' AND ' . $tableNameOrAlias . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['delete'] . '=0';
					}
					$additionalWhereClause .= '))';
					throw new Exception('Not tested code! It will fail', 1412928284);
				}
				$statementParts['additionalWhereClause'][$tableNameOrAlias][] = '(' . $additionalWhereClause . ')';
			}
		}
	}

	/**
	 * Builds the page ID checking statement
	 *
	 * @param string $tableNameOrAlias The database table name
	 * @param array &$statementParts The query parts
	 * @param array $storagePageIds list of storage page ids
	 * @throws Exception\InconsistentQuerySettingsException
	 * @return void
	 */
	protected function addPageIdStatement($tableNameOrAlias, array &$statementParts, array $storagePageIds) {

		$tableName = $this->resolveTableNameAlias($tableNameOrAlias);
		$tableColumns = $this->tableColumnCache->get($tableName);
		if ($tableColumns === FALSE) {
			$tableColumns = $this->databaseHandle->admin_get_fields($tableName);
			$this->tableColumnCache->set($tableName, $tableColumns);
		}
		if (is_array($GLOBALS['TCA'][$tableName]['ctrl']) && array_key_exists('pid', $tableColumns)) {
			$rootLevel = (int)$GLOBALS['TCA'][$tableName]['ctrl']['rootLevel'];
			if ($rootLevel) {
				if ($rootLevel === 1) {
					$statementParts['additionalWhereClause'][$tableNameOrAlias][] = $tableNameOrAlias . '.pid = 0';
				}
			} else {
				if (empty($storagePageIds)) {
					throw new Exception\InconsistentQuerySettingsException('Missing storage page ids.', 1365779762);
				}
				$statementParts['additionalWhereClause'][$tableNameOrAlias][] = $tableNameOrAlias . '.pid IN (' . implode(', ', $storagePageIds) . ')';
			}
		}
	}

	/**
	 * Transforms orderings into SQL.
	 *
	 * @param array $orderings An array of orderings (Tx_Extbase_Persistence_QOM_Ordering)
	 * @param SourceInterface $source The source
	 * @param array &$sql The query parts
	 * @throws Exception\UnsupportedOrderException
	 * @return void
	 */
	protected function parseOrderings(array $orderings, SourceInterface $source, array &$sql) {
		foreach ($orderings as $fieldNameAndPath => $order) {
			switch ($order) {
				case QueryInterface::ORDER_ASCENDING:
					$order = 'ASC';
					break;
				case QueryInterface::ORDER_DESCENDING:
					$order = 'DESC';
					break;
				default:
					throw new Exception\UnsupportedOrderException('Unsupported order encountered.', 1242816074);
			}

			$tableName = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $this->query->getType());
			$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $tableName);
			$sql['orderings'][] = sprintf('%s.%s %s', $tableName, $fieldName, $order);
		}
	}

	/**
	 * Transforms limit and offset into SQL
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array &$sql
	 * @return void
	 */
	protected function parseLimitAndOffset($limit, $offset, array &$sql) {
		if ($limit !== NULL && $offset !== NULL) {
			$sql['limit'] = intval($offset) . ', ' . intval($limit);
		} elseif ($limit !== NULL) {
			$sql['limit'] = intval($limit);
		}
	}

	/**
	 * Transforms a Resource from a database query to an array of rows.
	 *
	 * @param resource $result The result
	 * @return array The result as an array of rows (tuples)
	 */
	protected function getRowsFromResult($result) {
		$rows = array();
		while ($row = $this->databaseHandle->sql_fetch_assoc($result)) {
			if (is_array($row)) {

				// Get language uid from querySettings.
				// Ensure the backend handling is not broken (fallback to Get parameter 'L' if needed)
				$overlaidRow = $this->doLanguageAndWorkspaceOverlay($this->query->getSource(), $row, $this->query->getQuerySettings());

				$overlaidRow = GeneralUtility::makeInstance($this->objectType, $this->query->getType(), $overlaidRow);

				$rows[] = $overlaidRow;
			}
		}

		return $rows;
	}

	/**
	 * Performs workspace and language overlay on the given row array. The language and workspace id is automatically
	 * detected (depending on FE or BE context). You can also explicitly set the language/workspace id.
	 *
	 * @param SourceInterface $source The source (selector od join)
	 * @param array $row
	 * @param QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
	 * @return array
	 */
	protected function doLanguageAndWorkspaceOverlay(SourceInterface $source, array $row, $querySettings) {

		/** @var SelectorInterface $source */
		$tableName = $source->getSelectorName();

		$pageRepository = $this->getPageRepository();
		if (is_object($GLOBALS['TSFE'])) {
			$languageMode = $GLOBALS['TSFE']->sys_language_mode;
			if ($this->isBackendUserLogged() && $this->getBackendUser()->workspace !== 0) {
				$pageRepository->versioningWorkspaceId = $this->getBackendUser()->workspace;
			}
		} else {
			$languageMode = '';
			$workspaceUid = $this->getBackendUser()->workspace;
			$pageRepository->versioningWorkspaceId = $workspaceUid;
			if ($this->getBackendUser()->workspace !== 0) {
				$pageRepository->versioningPreview = 1;
			}
		}

		// If current row is a translation select its parent
		if (isset($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])
			&& isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])
		) {
			if (isset($row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']])
				&& $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']] > 0
			) {
				$row = $this->databaseHandle->exec_SELECTgetSingleRow(
					$tableName . '.*',
					$tableName,
					$tableName . '.uid=' . (int)$row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']] .
						' AND ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . '=0'
				);
			}
		}

		// Retrieve the original uid
		// @todo It looks for me this code will never be used! "_ORIG_uid" is something from extbase. Adjust me or remove me in 0.4 + 2 version!
		$pageRepository->versionOL($tableName, $row, TRUE);
		if ($pageRepository->versioningPreview && isset($row['_ORIG_uid'])) {
			$row['uid'] = $row['_ORIG_uid'];
		}

		// Special case for table "pages"
		if ($tableName == 'pages') {
			$row = $pageRepository->getPageOverlay($row, $querySettings->getLanguageUid());
		} elseif (isset($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])
			&& $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] !== ''
		) {
			if (in_array($row[$GLOBALS['TCA'][$tableName]['ctrl']['languageField']], array(-1, 0))) {
				$overlayMode = $languageMode === 'strict' ? 'hideNonTranslated' : '';
				$row = $pageRepository->getRecordOverlay($tableName, $row, $querySettings->getLanguageUid(), $overlayMode);
			}
		}

		return $row;
	}

	/**
	 * Return a resolved table name given a possible table name alias.
	 *
	 * @param string $tableNameOrAlias
	 * @return string
	 */
	protected function resolveTableNameAlias($tableNameOrAlias) {
		$resolvedTableName = $tableNameOrAlias;
		if (!empty($this->tableNameAliases['aliases'][$tableNameOrAlias])) {
			$resolvedTableName = $this->tableNameAliases['aliases'][$tableNameOrAlias];
		}
		return $resolvedTableName;
	}

	/**
	 * Generate a unique table name alias for the given table name.
	 *
	 * @param string $tableName
	 * @return string
	 */
	protected function generateAlias($tableName) {

		if (!isset($this->tableNameAliases['aliasIncrement'][$tableName])) {
			$this->tableNameAliases['aliasIncrement'][$tableName] = 0;
		}

		$numberOfAliases = $this->tableNameAliases['aliasIncrement'][$tableName];
		$tableNameAlias = $tableName . $numberOfAliases;

		$this->tableNameAliases['aliasIncrement'][$tableName]++;
		$this->tableNameAliases['aliases'][$tableNameAlias] = $tableName;

		return $tableNameAlias;
	}

	/**
	 * Replace the table names by its table name alias within the given statement.
	 *
	 * @param string $tableName
	 * @param string $tableNameAlias
	 * @param string $statement
	 * @return string
	 */
	protected function replaceTableNameByAlias($tableName, $tableNameAlias, $statement) {
		if ($statement && $tableName !== $tableNameAlias) {
			$statement = str_replace($tableName, $tableNameAlias, $statement);
		}
		return $statement;
	}

	/**
	 * Returns an instance of the current Backend User.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Tell whether a Backend User is logged in.
	 *
	 * @return bool
	 */
	protected function isBackendUserLogged() {
		return is_object($GLOBALS['BE_USER']);
	}

	/**
	 * @return PageRepository
	 */
	protected function getPageRepository() {
		if (!$this->pageRepository instanceof PageRepository) {
			if ($this->environmentService->isEnvironmentInFrontendMode() && is_object($GLOBALS['TSFE'])) {
				$this->pageRepository = $GLOBALS['TSFE']->sys_page;
			} else {
				$this->pageRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
			}
		}

		return $this->pageRepository;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}

	/**
	 * Checks if there are SQL errors in the last query, and if yes, throw an exception.
	 *
	 * @return void
	 * @param string $sql The SQL statement
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException
	 */
	protected function checkSqlErrors($sql = '') {
		$error = $this->databaseHandle->sql_error();
		if ($error !== '') {
			$error .= $sql ? ': ' . $sql : '';
			throw new \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception\SqlErrorException($error, 1247602160);
		}
	}
}
