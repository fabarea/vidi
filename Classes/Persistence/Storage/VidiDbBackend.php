<?php

namespace Fab\Vidi\Persistence\Storage;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\AndInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\NotInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidRelationConfigurationException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InconsistentQuerySettingsException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedOrderException;
use Fab\Vidi\Domain\Model\Content;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use Fab\Vidi\Resolver\FieldPathResolver;
use Doctrine\DBAL\ParameterType;
use Fab\Vidi\Persistence\Query;
use Fab\Vidi\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Versioning\VersionState;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\DynamicOperandInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\JoinInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LowerCaseInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValueInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\UpperCaseInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use Fab\Vidi\Tca\Tca;

/**
 * A Storage backend
 */
class VidiDbBackend
{
    public const OPERATOR_EQUAL_TO_NULL = 'operatorEqualToNull';
    public const OPERATOR_NOT_EQUAL_TO_NULL = 'operatorNotEqualToNull';

    /**
     * The TYPO3 page repository. Used for language and workspace overlay
     *
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var Query
     */
    protected $query;

    /**
     * Store some info related to table name and its aliases.
     *
     * @var array
     */
    protected $tableNameAliases = array(
        'aliases' => [],
        'aliasIncrement' => [],
    );

    /**
     * Use to store the current foreign table name alias.
     *
     * @var string
     */
    protected $currentChildTableNameAlias = '';

    /**
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * @param $parameters
     * @return array
     */
    protected static function getTypes($parameters)
    {
        $types = [];
        foreach ($parameters as $parameter) {
            if (is_array($parameter)) {
                if (MathUtility::canBeInterpretedAsInteger($parameter[0])) {
                    $types[] = \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
                } else {
                    $types[] = \Doctrine\DBAL\Connection::PARAM_STR_ARRAY;
                }
            } else {
                if (MathUtility::canBeInterpretedAsInteger($parameter)) {
                    $types[] = ParameterType::INTEGER;
                } else {
                    $types[] = ParameterType::STRING;
                }
            }
        }
        return $types;
    }

    /**
     * Returns the result of the query
     */
    public function fetchResult()
    {
        $parameters = [];
        $statementParts = $this->parseQuery($parameters);
        $statementParts = $this->processStatementStructureForRecursiveMMRelation($statementParts);
        $sql = $this->buildQuery($statementParts);
        //print $sql; exit();

        $rows = $this->getConnection()
            ->executeQuery($sql, $parameters, self::getTypes($parameters))
            ->fetchAllAssociative();

        return $this->getContentObjects($rows);
    }

    /**
     * Returns the number of tuples matching the query.
     *
     * @return int The number of matching tuples
     */
    public function countResult()
    {
        $parameters = [];
        $statementParts = $this->parseQuery($parameters);
        $statementParts = $this->processStatementStructureForRecursiveMMRelation($statementParts);
        $types = self::getTypes($parameters);

        // if limit is set, we need to count the rows "manually" as COUNT(*) ignores LIMIT constraints
        if (!empty($statementParts['limit'])) {
            $sql = $this->buildQuery($statementParts);

            $count = $this
                ->getConnection()
                ->executeQuery($sql, $parameters, $types)
                ->rowCount();
        } else {
            $statementParts['fields'] = array('COUNT(*)');
            // having orderings without grouping is not compatible with non-MySQL DBMS
            $statementParts['orderings'] = [];
            if (isset($statementParts['keywords']['distinct'])) {
                unset($statementParts['keywords']['distinct']);
                $distinctField = $this->query->getDistinct() ? $this->query->getDistinct() : 'uid';
                $statementParts['fields'] = array('COUNT(DISTINCT ' . $statementParts['mainTable'] . '.' . $distinctField . ')');
            }

            $sql = $this->buildQuery($statementParts);
            $count = $this
                ->getConnection()
                ->executeQuery($sql, $parameters, $types)
                ->fetchColumn(0);
        }
        return (int)$count;
    }

    /**
     * Parses the query and returns the SQL statement parts.
     *
     * @param array &$parameters
     * @return array
     */
    public function parseQuery(array &$parameters)
    {
        $statementParts = [];
        $statementParts['keywords'] = [];
        $statementParts['tables'] = [];
        $statementParts['unions'] = [];
        $statementParts['fields'] = [];
        $statementParts['where'] = [];
        $statementParts['additionalWhereClause'] = [];
        $statementParts['orderings'] = [];
        $statementParts['limit'] = [];
        $query = $this->query;
        $source = $query->getSource();
        $this->parseSource($source, $statementParts);
        $this->parseConstraint($query->getConstraint(), $source, $statementParts, $parameters);
        $this->parseOrderings($query->getOrderings(), $source, $statementParts);
        $this->parseLimitAndOffset($query->getLimit(), $query->getOffset(), $statementParts);
        $tableNames = array_unique(array_keys($statementParts['tables'] + $statementParts['unions']));
        foreach ($tableNames as $tableNameOrAlias) {
            if (is_string($tableNameOrAlias) && strlen($tableNameOrAlias) > 0) {
                $this->addAdditionalWhereClause($query->getTypo3QuerySettings(), $tableNameOrAlias, $statementParts);
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
    public function processStatementStructureForRecursiveMMRelation(array $statementParts)
    {
        if ($this->hasRecursiveMMRelation()) {
            $tableName = $this->query->getType();

            // In order the MM query to work for a recursive MM query, we must invert some values.
            // tx_domain_model_foo0 (the alias) <--> tx_domain_model_foo (the origin table name)
            $values = [];
            foreach ($statementParts['fields'] as $key => $value) {
                $values[$key] = str_replace($tableName, $tableName . '0', $value);
            }
            $statementParts['fields'] = $values;

            // Same comment as above.
            $values = [];
            foreach ($statementParts['where'] as $key => $value) {
                $values[$key] = str_replace($tableName . '0', $tableName, $value);
            }
            $statementParts['where'] = $values;

            // We must be more restrictive by transforming the "left" union by "inner"
            $values = [];
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
    public function hasRecursiveMMRelation()
    {
        return isset($this->tableNameAliases['aliasIncrement'][$this->query->getType()]);
    }

    /**
     * Returns the statement, ready to be executed.
     *
     * @param array $statementParts The SQL statement parts
     * @return string The SQL statement
     */
    public function buildQuery(array $statementParts)
    {
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
    protected function parseSource(SourceInterface $source, array &$sql)
    {
        $tableName = $this->getTableName();
        $sql['fields'][$tableName] = $tableName . '.*';
        if ($this->query->getDistinct()) {
            $sql['fields'][$tableName] = $tableName . '.' . $this->query->getDistinct();
            $sql['keywords']['distinct'] = 'DISTINCT';
        }
        $sql['tables'][$tableName] = $tableName;
        $sql['mainTable'] = $tableName;
    }

    /**
     * Transforms a constraint into SQL and parameter arrays
     *
     * @param ConstraintInterface $constraint The constraint
     * @param SourceInterface $source The source
     * @param array &$statementParts The query parts
     * @param array &$parameters The parameters that will replace the markers
     * @return void
     */
    protected function parseConstraint(ConstraintInterface $constraint = null, SourceInterface $source, array &$statementParts, array &$parameters)
    {
        if ($constraint instanceof AndInterface) {
            $statementParts['where'][] = '(';
            $this->parseConstraint($constraint->getConstraint1(), $source, $statementParts, $parameters);
            $statementParts['where'][] = ' AND ';
            $this->parseConstraint($constraint->getConstraint2(), $source, $statementParts, $parameters);
            $statementParts['where'][] = ')';
        } elseif ($constraint instanceof OrInterface) {
            $statementParts['where'][] = '(';
            $this->parseConstraint($constraint->getConstraint1(), $source, $statementParts, $parameters);
            $statementParts['where'][] = ' OR ';
            $this->parseConstraint($constraint->getConstraint2(), $source, $statementParts, $parameters);
            $statementParts['where'][] = ')';
        } elseif ($constraint instanceof NotInterface) {
            $statementParts['where'][] = 'NOT (';
            $this->parseConstraint($constraint->getConstraint(), $source, $statementParts, $parameters);
            $statementParts['where'][] = ')';
        } elseif ($constraint instanceof ComparisonInterface) {
            $this->parseComparison($constraint, $source, $statementParts, $parameters);
        }
    }

    /**
     * Parse a Comparison into SQL and parameter arrays.
     *
     * @param ComparisonInterface $comparison The comparison to parse
     * @param SourceInterface $source The source
     * @param array &$statementParts SQL query parts to add to
     * @param array &$parameters Parameters to bind to the SQL
     * @return void
     * @throws Exception\RepositoryException
     */
    protected function parseComparison(ComparisonInterface $comparison, SourceInterface $source, array &$statementParts, array &$parameters)
    {
        $operand1 = $comparison->getOperand1();
        $operator = $comparison->getOperator();
        $operand2 = $comparison->getOperand2();
        if ($operator === QueryInterface::OPERATOR_IN) {
            $items = [];
            $hasValue = false;
            foreach ($operand2 as $value) {
                $value = $this->getPlainValue($value);
                if ($value !== null) {
                    $items[] = $value;
                    $hasValue = true;
                }
            }
            if ($hasValue === false) {
                $statementParts['where'][] = '1<>1';
            } else {
                $this->parseDynamicOperand($operand1, $operator, $source, $statementParts, $parameters, null);
                $parameters[] = $items;
            }
        } elseif ($operator === QueryInterface::OPERATOR_CONTAINS) {
            if ($operand2 === null) {
                $statementParts['where'][] = '1<>1';
            } else {
                throw new \Exception('Not implemented! Contact extension author.', 1412931227);
                # @todo re-implement me if necessary.
                #$tableName = $this->query->getType();
                #$propertyName = $operand1->getPropertyName();
                #while (strpos($propertyName, '.') !== false) {
                #	$this->addUnionStatement($tableName, $propertyName, $statementParts);
                #}
                #$columnName = $propertyName;
                #$columnMap = $propertyName;
                #$typeOfRelation = $columnMap instanceof ColumnMap ? $columnMap->getTypeOfRelation() : null;
                #if ($typeOfRelation === ColumnMap::RELATION_HAS_AND_BELONGS_TO_MANY) {
                #	$relationTableName = $columnMap->getRelationTableName();
                #	$statementParts['where'][] = $tableName . '.uid IN (SELECT ' . $columnMap->getParentKeyFieldName() . ' FROM ' . $relationTableName . ' WHERE ' . $columnMap->getChildKeyFieldName() . '=?)';
                #	$parameters[] = intval($this->getPlainValue($operand2));
                #} elseif ($typeOfRelation === ColumnMap::RELATION_HAS_MANY) {
                #	$parentKeyFieldName = $columnMap->getParentKeyFieldName();
                #	if (isset($parentKeyFieldName)) {
                #		$childTableName = $columnMap->getChildTableName();
                #		$statementParts['where'][] = $tableName . '.uid=(SELECT ' . $childTableName . '.' . $parentKeyFieldName . ' FROM ' . $childTableName . ' WHERE ' . $childTableName . '.uid=?)';
                #		$parameters[] = intval($this->getPlainValue($operand2));
                #	} else {
                #		$statementParts['where'][] = 'FIND_IN_SET(?,' . $tableName . '.' . $columnName . ')';
                #		$parameters[] = intval($this->getPlainValue($operand2));
                #	}
                #} else {
                #	throw new Exception\RepositoryException('Unsupported or non-existing property name "' . $propertyName . '" used in relation matching.', 1327065745);
                #}
            }
        } else {
            if ($operand2 === null) {
                if ($operator === QueryInterface::OPERATOR_EQUAL_TO) {
                    $operator = self::OPERATOR_EQUAL_TO_NULL;
                } elseif ($operator === QueryInterface::OPERATOR_NOT_EQUAL_TO) {
                    $operator = self::OPERATOR_NOT_EQUAL_TO_NULL;
                }
            }
            $this->parseDynamicOperand($operand1, $operator, $source, $statementParts, $parameters);
            $parameters[] = $this->getPlainValue($operand2);
        }
    }

    /**
     * Returns a plain value, i.e. objects are flattened if possible.
     *
     * @param mixed $input
     * @return mixed
     * @throws UnexpectedTypeException
     */
    protected function getPlainValue($input)
    {
        if (is_array($input)) {
            throw new UnexpectedTypeException('An array could not be converted to a plain value.', 1274799932);
        }
        if ($input instanceof \DateTime) {
            return $input->format('U');
        } elseif (is_object($input)) {
            if ($input instanceof LazyLoadingProxy) {
                $realInput = $input->_loadRealInstance();
            } else {
                $realInput = $input;
            }
            if ($realInput instanceof DomainObjectInterface) {
                return $realInput->getUid();
            } else {
                throw new UnexpectedTypeException('An object of class "' . get_class($realInput) . '" could not be converted to a plain value.', 1274799934);
            }
        } elseif (is_bool($input)) {
            return $input === true ? 1 : 0;
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
     * @param array &$statementParts The query parts
     * @param array &$parameters The parameters that will replace the markers
     * @param string $valueFunction an optional SQL function to apply to the operand value
     * @return void
     */
    protected function parseDynamicOperand(DynamicOperandInterface $operand, $operator, SourceInterface $source, array &$statementParts, array &$parameters, $valueFunction = null)
    {
        if ($operand instanceof LowerCaseInterface) {
            $this->parseDynamicOperand($operand->getOperand(), $operator, $source, $statementParts, $parameters, 'LOWER');
        } elseif ($operand instanceof UpperCaseInterface) {
            $this->parseDynamicOperand($operand->getOperand(), $operator, $source, $statementParts, $parameters, 'UPPER');
        } elseif ($operand instanceof PropertyValueInterface) {
            $propertyName = $operand->getPropertyName();

            // Reset value.
            $this->currentChildTableNameAlias = '';

            if ($source instanceof SelectorInterface) {
                $tableName = $this->query->getType();
                while (strpos($propertyName, '.') !== false) {
                    $this->addUnionStatement($tableName, $propertyName, $statementParts);
                }
            } elseif ($source instanceof JoinInterface) {
                $tableName = $source->getJoinCondition()->getSelector1Name();
            }

            $columnName = $propertyName;
            $resolvedOperator = $this->resolveOperator($operator);
            $constraintSQL = '';

            $marker = $operator === QueryInterface::OPERATOR_IN
                ? '(?)'
                : '?';

            if ($valueFunction === null) {
                $constraintSQL .= (!empty($tableName) ? $tableName . '.' : '') . $columnName . ' ' . $resolvedOperator . ' ' . $marker;
            } else {
                $constraintSQL .= $valueFunction . '(' . (!empty($tableName) ? $tableName . '.' : '') . $columnName . ') ' . $resolvedOperator . ' ' . $marker;
            }

            if (isset($tableName) && !empty($this->currentChildTableNameAlias)) {
                $constraintSQL = $this->replaceTableNameByAlias($tableName, $this->currentChildTableNameAlias, $constraintSQL);
            }
            $statementParts['where'][] = $constraintSQL;
        }
    }

    /**
     * @param string &$tableName
     * @param string &$propertyPath
     * @param array &$statementParts
     */
    protected function addUnionStatement(&$tableName, &$propertyPath, array &$statementParts)
    {
        $table = Tca::table($tableName);

        $explodedPropertyPath = explode('.', $propertyPath, 2);
        $fieldName = $explodedPropertyPath[0];

        // Field of type "group" are special because property path must contain the table name
        // to determine the relation type. Example for sys_category, property path will look like "items.sys_file"
        $parts = explode('.', $propertyPath, 3);
        if ($table->field($fieldName)->isGroup() && count($parts) > 2) {
            $explodedPropertyPath[0] = $parts[0] . '.' . $parts[1];
            $explodedPropertyPath[1] = $parts[2];
            $fieldName = $explodedPropertyPath[0];
        }

        $parentKeyFieldName = $table->field($fieldName)->getForeignField();
        $childTableName = $table->field($fieldName)->getForeignTable();

        if ($childTableName === null) {
            throw new InvalidRelationConfigurationException('The relation information for property "' . $fieldName . '" of class "' . $tableName . '" is missing.', 1353170925);
        }

        if ($table->field($fieldName)->hasOne()) { // includes relation "one-to-one" and "many-to-one"
            // sometimes the opposite relation is not defined. We don't want to force this config for backward compatibility reasons.
            // $parentKeyFieldName === null does the trick somehow. Before condition was if (isset($parentKeyFieldName))
            if ($table->field($fieldName)->hasRelationManyToOne() || $parentKeyFieldName === null) {
                $statementParts['unions'][$childTableName] = 'LEFT JOIN ' . $childTableName . ' ON ' . $tableName . '.' . $fieldName . '=' . $childTableName . '.uid';
            } else {
                $statementParts['unions'][$childTableName] = 'LEFT JOIN ' . $childTableName . ' ON ' . $tableName . '.uid=' . $childTableName . '.' . $parentKeyFieldName;
            }
        } elseif ($table->field($fieldName)->hasRelationManyToMany()) {
            $relationTableName = $table->field($fieldName)->getManyToManyTable();

            $parentKeyFieldName = $table->field($fieldName)->isOppositeRelation() ? 'uid_foreign' : 'uid_local';
            $childKeyFieldName = !$table->field($fieldName)->isOppositeRelation() ? 'uid_foreign' : 'uid_local';

            // MM table e.g sys_category_record_mm
            $relationTableNameAlias = $this->generateAlias($relationTableName);
            $join = sprintf(
                'LEFT JOIN %s AS %s ON %s.uid=%s.%s',
                $relationTableName,
                $relationTableNameAlias,
                $tableName,
                $relationTableNameAlias,
                $parentKeyFieldName
            );
            $statementParts['unions'][$relationTableNameAlias] = $join;

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
            $statementParts['unions'][$childTableNameAlias] = $join;

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
                    $additionalMMConditions = Tca::table($tableNameCondition)->field($sourceFileName)->getAdditionalMMCondition();
                }

                foreach ($additionalMMConditions as $additionalFieldName => $additionalMMCondition) {
                    $additionalJoin = sprintf(' AND %s.%s = "%s"', $relationTableNameAlias, $additionalFieldName, $additionalMMCondition);
                    $statementParts['unions'][$relationTableNameAlias] .= $additionalJoin;

                    $additionalJoin = sprintf(' AND %s.%s = "%s"', $relationTableNameAlias, $additionalFieldName, $additionalMMCondition);
                    $statementParts['unions'][$childTableNameAlias] .= $additionalJoin;
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
                $statementParts['unions'][$childTableNameAlias] = $join;
            } else {
                $join = sprintf(
                    'LEFT JOIN %s AS %s ON (FIND_IN_SET(%s.uid, %s.%s))',
                    $childTableName,
                    $childTableNameAlias,
                    $childTableNameAlias,
                    $tableName,
                    $fieldName
                );
                $statementParts['unions'][$childTableNameAlias] = $join;
            }
        } else {
            throw new Exception('Could not determine type of relation.', 1252502725);
        }

        $statementParts['keywords']['distinct'] = 'DISTINCT';
        $propertyPath = $explodedPropertyPath[1];
        $tableName = $childTableName;
    }

    /**
     * Returns the SQL operator for the given JCR operator type.
     *
     * @param string $operator One of the JCR_OPERATOR_* constants
     * @return string an SQL operator
     * @throws Exception
     */
    protected function resolveOperator($operator)
    {
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
     * Adds additional WHERE statements according to the query settings.
     *
     * @param QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
     * @param string $tableNameOrAlias The table name to add the additional where clause for
     * @param array &$statementParts
     * @return void
     */
    protected function addAdditionalWhereClause(QuerySettingsInterface $querySettings, $tableNameOrAlias, &$statementParts)
    {
        $this->addVisibilityConstraintStatement($querySettings, $tableNameOrAlias, $statementParts);
        if ($querySettings->getRespectSysLanguage()) {
            $this->addSysLanguageStatement($tableNameOrAlias, $statementParts, $querySettings);
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
    protected function addVisibilityConstraintStatement(QuerySettingsInterface $querySettings, $tableNameOrAlias, array &$statementParts)
    {
        $statement = '';
        $tableName = $this->resolveTableNameAlias($tableNameOrAlias);
        if (is_array($GLOBALS['TCA'][$tableName]['ctrl'])) {
            $ignoreEnableFields = $querySettings->getIgnoreEnableFields();
            $enableFieldsToBeIgnored = $querySettings->getEnableFieldsToBeIgnored();
            $includeDeleted = $querySettings->getIncludeDeleted();
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
                $statement .= $this->getFrontendConstraintStatement($tableNameOrAlias, $ignoreEnableFields, $enableFieldsToBeIgnored, $includeDeleted);
            } else {
                // 'BE' case
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
     * @param array $enableFieldsToBeIgnored If $ignoreEnableFields is true, this array specifies enable fields to be ignored. If it is null or an empty array (default) all enable fields are ignored.
     * @param boolean $includeDeleted A flag indicating whether deleted records should be included
     * @return string
     * @throws Exception\InconsistentQuerySettingsException
     */
    protected function getFrontendConstraintStatement($tableNameOrAlias, $ignoreEnableFields, $enableFieldsToBeIgnored, $includeDeleted)
    {
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
            throw new InconsistentQuerySettingsException('Query setting "ignoreEnableFields=false" can not be used together with "includeDeleted=true" in frontend context.', 1327678173);
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
    protected function getBackendConstraintStatement($tableNameOrAlias, $ignoreEnableFields, $includeDeleted)
    {
        $tableName = $this->resolveTableNameAlias($tableNameOrAlias);
        $statement = '';
        if (!$ignoreEnableFields) {
            $statement .= BackendUtility::BEenableFields($tableName);
        }

        // If the table is found to have "workspace" support, add the corresponding fields in the statement.
        if (Tca::table($tableName)->hasWorkspaceSupport()) {
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
     * @return void
     * @throws Exception
     */
    protected function addSysLanguageStatement($tableNameOrAlias, array &$statementParts, $querySettings)
    {
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
                    $additionalWhereClause .= ' OR (' . $tableNameOrAlias . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . '=0' .
                        ' AND ' . $tableNameOrAlias . '.uid NOT IN (SELECT ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] .
                        ' FROM ' . $tableName .
                        ' WHERE ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] . '>0' .
                        ' AND ' . $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . '>0';

                    // Add delete clause to ensure all entries are loaded
                    if (isset($GLOBALS['TCA'][$tableName]['ctrl']['delete'])) {
                        $additionalWhereClause .= ' AND ' . $tableNameOrAlias . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['delete'] . '=0';
                    }
                    $additionalWhereClause .= '))';
                }
                $statementParts['additionalWhereClause'][$tableNameOrAlias][] = '(' . $additionalWhereClause . ')';
            }
        }
    }

    /**
     * Transforms orderings into SQL.
     *
     * @param array $orderings An array of orderings (Tx_Extbase_Persistence_QOM_Ordering)
     * @param SourceInterface $source The source
     * @param array &$statementParts The query parts
     * @return void
     * @throws Exception\UnsupportedOrderException
     */
    protected function parseOrderings(array $orderings, SourceInterface $source, array &$statementParts)
    {
        foreach ($orderings as $fieldNameAndPath => $order) {
            switch ($order) {
                case QueryInterface::ORDER_ASCENDING:
                    $order = 'ASC';
                    break;
                case QueryInterface::ORDER_DESCENDING:
                    $order = 'DESC';
                    break;
                default:
                    throw new UnsupportedOrderException('Unsupported order encountered.', 1456845126);
            }

            $tableName = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $this->query->getType());
            $fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $tableName);
            $statementParts['orderings'][] = sprintf('%s.%s %s', $tableName, $fieldName, $order);
        }
    }

    /**
     * Transforms limit and offset into SQL
     *
     * @param int $limit
     * @param int $offset
     * @param array &$statementParts
     * @return void
     */
    protected function parseLimitAndOffset($limit, $offset, array &$statementParts)
    {
        if ($limit !== null && $offset !== null) {
            $statementParts['limit'] = intval($offset) . ', ' . intval($limit);
        } elseif ($limit !== null) {
            $statementParts['limit'] = intval($limit);
        }
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function getContentObjects(array $rows): array
    {
        $contentObjects = [];
        foreach ($rows as $row) {
            // Get language uid from querySettings.
            // Ensure the backend handling is not broken (fallback to Get parameter 'L' if needed)
            $overlaidRow = $this->doLanguageAndWorkspaceOverlay(
                $row,
                $this->query->getTypo3QuerySettings()
            );

            $contentObjects[] = GeneralUtility::makeInstance(
                Content::class,
                $this->query->getType(),
                $overlaidRow
            );
        }

        return $contentObjects;
    }

    /**
     * Performs workspace and language overlay on the given row array. The language and workspace id is automatically
     * detected (depending on FE or BE context). You can also explicitly set the language/workspace id.
     *
     * @param array $row
     * @param QuerySettingsInterface $querySettings The TYPO3 CMS specific query settings
     * @return array
     */
    protected function doLanguageAndWorkspaceOverlay(array $row, $querySettings)
    {
        $tableName = $this->getTableName();

        $pageRepository = $this->getPageRepository();
        if (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
            $languageMode = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'legacyLanguageMode');
        #if ($this->isBackendUserLogged() && $this->getBackendUser()->workspace !== 0) {
            #    $pageRepository->versioningWorkspaceId = $this->getBackendUser()->workspace;
        #}
        } else {
            $languageMode = '';
            $workspaceUid = $this->getBackendUser()->workspace;
            #$pageRepository->versioningWorkspaceId = $workspaceUid;
            #if ($this->getBackendUser()->workspace !== 0) {
            #    $pageRepository->versioningPreview = 1;
            #}
        }

        // If current row is a translation select its parent
        if (isset($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])
            && isset($GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'])
        ) {
            if (isset($row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']])
                && $row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']] > 0
            ) {
                $queryBuilder = $this->getQueryBuilder();
                $row = $queryBuilder
                    ->select($tableName . '.*')
                    ->from($tableName)
                    ->andWhere(
                        $tableName . '.uid=' . (int)$row[$GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField']],
                        $tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] . ' = 0'
                    )
                    ->execute()
                    ->fetch();
            }
        }

        // Retrieve the original uid; Used for Workspaces!
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            $pageRepository->versionOL($tableName, $row, true, true);
        } else {
            \TYPO3\CMS\Backend\Utility\BackendUtility::workspaceOL($tableName, $row);
        }
        if (isset($row['_ORIG_uid'])) {
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
    protected function resolveTableNameAlias($tableNameOrAlias)
    {
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
    protected function generateAlias($tableName)
    {
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
    protected function replaceTableNameByAlias($tableName, $tableNameAlias, $statement)
    {
        if ($statement && $tableName !== $tableNameAlias) {
            $statement = str_replace($tableName, $tableNameAlias, $statement);
        }
        return $statement;
    }

    /**
     * Returns an instance of the current Backend User.
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Tell whether a Backend User is logged in.
     *
     * @return bool
     */
    protected function isBackendUserLogged()
    {
        return is_object($GLOBALS['BE_USER']);
    }

    /**
     * @return PageRepository|object
     */
    protected function getPageRepository()
    {
        if (!$this->pageRepository instanceof PageRepository) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend() && is_object($GLOBALS['TSFE'])) {
                $this->pageRepository = $GLOBALS['TSFE']->sys_page;
            } else {
                $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
            }
        }

        return $this->pageRepository;
    }

    /**
     * @return FieldPathResolver|object
     */
    protected function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance(FieldPathResolver::class);
    }

    /**
     * @return object|Connection
     */
    protected function getConnection(): Connection
    {
        /** @var ConnectionPool $connectionPool */
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($this->getTableName());
    }

    /**
     * @return object|QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->getTableName());
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->query->getSource()->getNodeTypeName(); // getSelectorName()
    }
}
