<?php
namespace Fab\Vidi\Persistence;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Persistence\Storage\VidiDbBackend;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\InvalidNumberOfConstraintsException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnexpectedTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * The Query class used to run queries against the database
 *
 * @api
 */
class Query implements QueryInterface
{
    /**
     * An inner join.
     */
    const JCR_JOIN_TYPE_INNER = '{http://www.jcp.org/jcr/1.0}joinTypeInner';

    /**
     * A left-outer join.
     */
    const JCR_JOIN_TYPE_LEFT_OUTER = '{http://www.jcp.org/jcr/1.0}joinTypeLeftOuter';

    /**
     * A right-outer join.
     */
    const JCR_JOIN_TYPE_RIGHT_OUTER = '{http://www.jcp.org/jcr/1.0}joinTypeRightOuter';

    /**
     * Charset of strings in QOM
     */
    const CHARSET = 'utf-8';

    /**
     * @var string
     */
    protected $sourceFieldName;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelFactory
     */
    protected $qomFactory;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface
     */
    protected $source;

    /**
     * @var ConstraintInterface
     */
    protected $constraint;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement
     */
    protected $statement;

    /**
     * @var array
     */
    protected $orderings = [];

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * Apply DISTINCT upon property.
     *
     * @var string
     */
    protected $distinct;

    /**
     * The query settings.
     *
     * @var \Fab\Vidi\Persistence\QuerySettings
     * @Inject
     */
    public $querySettings;

    /**
     * Constructs a query object working on the given class name
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Injects the persistence manager, used to fetch the CR session
     *
     * @param \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager
     * @return void
     */
    public function injectPersistenceManager(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Injects the Query Object Model Factory
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelFactory $qomFactory
     * @return void
     */
    public function injectQomFactory(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelFactory $qomFactory)
    {
        $this->qomFactory = $qomFactory;
    }

    /**
     * Sets the Query Settings. These Query settings must match the settings expected by
     * the specific Storage Backend.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The Query Settings
     * @return void
     * @api This method is not part of FLOW3 API
     */
    public function setQuerySettings(\TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings)
    {
        $this->querySettings = $querySettings;
    }

    /**
     * Returns the Query Settings.
     *
     * @throws \Exception
     * @return \Fab\Vidi\Persistence\QuerySettings $querySettings The Query Settings
     * @api This method is not part of FLOW3 API
     */
    public function getQuerySettings()
    {
        if (!$this->querySettings instanceof \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface) {
            throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception('Tried to get the query settings without setting them before.', 1248689115);
        }

        // Apply possible settings to the query.
        if ($this->isBackendMode()) {
            /** @var \TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager $backendConfigurationManager */
            $backendConfigurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager::class);
            $configuration = $backendConfigurationManager->getTypoScriptSetup();
            $querySettings = ['respectSysLanguage'];
            foreach ($querySettings as $setting) {
                if (isset($configuration['config.']['tx_vidi.']['persistence.']['backend.'][$this->type . '.'][$setting])) {
                    $value = (bool) $configuration['config.']['tx_vidi.']['persistence.']['backend.'][$this->type . '.'][$setting];
                    ObjectAccess::setProperty($this->querySettings, $setting, $value);
                }
            }
        }

        return $this->querySettings;
    }

    /**
     * Returns the type this query cares for.
     *
     * @return string
     * @api
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the source to fetch the result from
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source
     */
    public function setSource(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * Returns the selectorn name or an empty string, if the source is not a selector
     * TODO This has to be checked at another place
     *
     * @return string The selector name
     */
    protected function getSelectorName()
    {
        if ($this->getSource() instanceof \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SelectorInterface) {
            return $this->source->getSelectorName();
        } else {
            return '';
        }
    }

    /**
     * Gets the node-tuple source for this query.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\SourceInterface the node-tuple source; non-null
     */
    public function getSource()
    {
        if ($this->source === null) {
            $this->source = $this->qomFactory->selector($this->getType());
        }
        return $this->source;
    }

    /**
     * Executes the query against the database and returns the result
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is true
     * @api
     */
    public function execute($returnRawQueryResult = false)
    {
        /** @var VidiDbBackend $backend */
        $backend = GeneralUtility::makeInstance(VidiDbBackend::class, $this);
        return $backend->fetchResult();
    }

    /**
     * Sets the property names to order the result by. Expected like this:
     * array(
     * 'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     * 'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     * where 'foo' and 'bar' are property names.
     *
     * @param array $orderings The property names to order by
     * @return QueryInterface
     * @api
     */
    public function setOrderings(array $orderings)
    {
        $this->orderings = $orderings;
        return $this;
    }

    /**
     * Returns the property names to order the result by. Like this:
     * array(
     * 'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
     * 'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
     * )
     *
     * @return array
     */
    public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * Sets the maximum size of the result set to limit. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @param integer $limit
     * @throws \InvalidArgumentException
     * @return QueryInterface
     * @api
     */
    public function setLimit($limit)
    {
        if (!is_int($limit) || $limit < 1) {
            throw new \InvalidArgumentException('The limit must be an integer >= 1', 1245071870);
        }
        $this->limit = $limit;
        return $this;
    }

    /**
     * Resets a previously set maximum size of the result set. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @return QueryInterface
     * @api
     */
    public function unsetLimit()
    {
        unset($this->limit);
        return $this;
    }

    /**
     * Returns the maximum size of the result set to limit.
     *
     * @return integer
     * @api
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets the start offset of the result set to offset. Returns $this to
     * allow for chaining (fluid interface)
     *
     * @param integer $offset
     * @throws \InvalidArgumentException
     * @return QueryInterface
     * @api
     */
    public function setOffset($offset)
    {
        if (!is_int($offset) || $offset < 0) {
            throw new \InvalidArgumentException('The offset must be a positive integer', 1245071872);
        }
        $this->offset = $offset;
        return $this;
    }

    /**
     * Returns the start offset of the result set.
     *
     * @return integer
     * @api
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * The constraint used to limit the result set. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @param ConstraintInterface $constraint
     * @return QueryInterface
     * @api
     */
    public function matching($constraint)
    {
        $this->constraint = $constraint;
        return $this;
    }

    /**
     * Gets the constraint for this query.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface the constraint, or null if none
     * @api
     */
    public function getConstraint()
    {
        return $this->constraint;
    }

    /**
     * Performs a logical conjunction of the given constraints. The method takes one or more contraints and concatenates them with a boolean AND.
     * It also scepts a single array of constraints to be concatenated.
     *
     * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
     * @throws InvalidNumberOfConstraintsException
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\AndInterface
     * @api
     */
    public function logicalAnd($constraint1)
    {
        if (is_array($constraint1)) {
            $resultingConstraint = array_shift($constraint1);
            $constraints = $constraint1;
        } else {
            $constraints = func_get_args();
            $resultingConstraint = array_shift($constraints);
        }
        if ($resultingConstraint === null) {
            throw new InvalidNumberOfConstraintsException('There must be at least one constraint or a non-empty array of constraints given.', 1401289500);
        }
        foreach ($constraints as $constraint) {
            $resultingConstraint = $this->qomFactory->_and($resultingConstraint, $constraint);
        }
        return $resultingConstraint;
    }

    /**
     * Performs a logical disjunction of the two given constraints
     *
     * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
     * @throws InvalidNumberOfConstraintsException
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface
     * @api
     */
    public function logicalOr($constraint1)
    {
        if (is_array($constraint1)) {
            $resultingConstraint = array_shift($constraint1);
            $constraints = $constraint1;
        } else {
            $constraints = func_get_args();
            $resultingConstraint = array_shift($constraints);
        }
        if ($resultingConstraint === null) {
            throw new InvalidNumberOfConstraintsException('There must be at least one constraint or a non-empty array of constraints given.', 1401289501);
        }
        foreach ($constraints as $constraint) {
            $resultingConstraint = $this->qomFactory->_or($resultingConstraint, $constraint);
        }
        return $resultingConstraint;
    }

    /**
     * Performs a logical negation of the given constraint
     *
     * @param ConstraintInterface $constraint Constraint to negate
     * @throws \RuntimeException
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\NotInterface
     * @api
     */
    public function logicalNot(ConstraintInterface $constraint)
    {
        return $this->qomFactory->not($constraint);
    }

    /**
     * Returns an equals criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @param boolean $caseSensitive Whether the equality test should be done case-sensitive
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function equals($propertyName, $operand, $caseSensitive = true)
    {
        if (is_object($operand) || $caseSensitive) {
            $comparison = $this->qomFactory->comparison(
                $this->qomFactory->propertyValue($propertyName, $this->getSelectorName()),
                QueryInterface::OPERATOR_EQUAL_TO,
                $operand,
            );
        } else {
            $comparison = $this->qomFactory->comparison(
                $this->qomFactory->lowerCase($this->qomFactory->propertyValue($propertyName, $this->getSelectorName())),
                QueryInterface::OPERATOR_EQUAL_TO,
                \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Charset\CharsetConverter::class)->conv_case(
                    \TYPO3\CMS\Extbase\Persistence\Generic\Query::CHARSET,
                    $operand,
                    'toLower',
                ),
            );
        }
        return $comparison;
    }

    /**
     * Returns a like criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @param boolean $caseSensitive Whether the matching should be done case-sensitive
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function like($propertyName, $operand, $caseSensitive = true)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getSelectorName()),
            QueryInterface::OPERATOR_LIKE,
            $operand,
        );
    }

    /**
     * Returns a "contains" criterion used for matching objects against a query.
     * It matches if the multivalued property contains the given operand.
     *
     * @param string $propertyName The name of the (multivalued) property to compare against
     * @param mixed $operand The value to compare with
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function contains($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getSelectorName()),
            QueryInterface::OPERATOR_CONTAINS,
            $operand,
        );
    }

    /**
     * Returns an "in" criterion used for matching objects against a query. It
     * matches if the property's value is contained in the multivalued operand.
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with, multivalued
     * @throws UnexpectedTypeException
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function in($propertyName, $operand)
    {
        if (!is_array($operand) && !$operand instanceof \ArrayAccess && !$operand instanceof \Traversable) {
            throw new UnexpectedTypeException('The "in" operator must be given a mutlivalued operand (array, ArrayAccess, Traversable).', 1264678095);
        }
        return $this->qomFactory->comparison($this->qomFactory->propertyValue($propertyName, $this->getSelectorName()), QueryInterface::OPERATOR_IN, $operand);
    }

    /**
     * Returns a less than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function lessThan($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getSelectorName()),
            QueryInterface::OPERATOR_LESS_THAN,
            $operand,
        );
    }

    /**
     * Returns a less or equal than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function lessThanOrEqual($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getSelectorName()),
            QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO,
            $operand,
        );
    }

    /**
     * Returns a greater than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function greaterThan($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getSelectorName()),
            QueryInterface::OPERATOR_GREATER_THAN,
            $operand,
        );
    }

    /**
     * Returns a greater than or equal criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface
     * @api
     */
    public function greaterThanOrEqual($propertyName, $operand)
    {
        return $this->qomFactory->comparison(
            $this->qomFactory->propertyValue($propertyName, $this->getSelectorName()),
            QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO,
            $operand,
        );
    }

    /**
     * Returns the query result count.
     *
     * @return integer The query result count
     * @api
     */
    public function count()
    {
        /** @var VidiDbBackend $backend */
        $backend = GeneralUtility::makeInstance(VidiDbBackend::class, $this);
        return $backend->countResult();
    }

    /**
     * Returns an "isEmpty" criterion used for matching objects against a query.
     * It matches if the multivalued property contains no values or is null.
     *
     * @param string $propertyName The name of the multivalued property to compare against
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException if used on a single-valued property
     * @api
     */
    public function isEmpty($propertyName)
    {
        throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException(__METHOD__);
    }

    /**
     * @return string
     */
    public function getDistinct()
    {
        return $this->distinct;
    }

    /**
     * @param string $distinct
     * @return $this
     */
    public function setDistinct($distinct)
    {
        $this->distinct = $distinct;
        return $this;
    }

    /**
     * Sets the statement of this query. If you use this, you will lose the abstraction from a concrete storage
     * backend (database).
     *
     * @param string|\TYPO3\CMS\Core\Database\PreparedStatement $statement The statement
     * @param array $parameters An array of parameters. These will be bound to placeholders '?' in the $statement.
     * @return QueryInterface
     */
    public function statement($statement, array $parameters = [])
    {
        $this->statement = $this->qomFactory->statement($statement, $parameters);
        return $this;
    }

    /**
     * Returns the statement of this query.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\Statement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * Returns whether the current mode is Backend.
     *
     * @return bool
     */
    protected function isBackendMode()
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }

    /**
     * @return string
     */
    public function getSourceFieldName()
    {
        return $this->sourceFieldName;
    }

    /**
     * @param string $sourceFieldName
     * @return $this
     */
    public function setSourceFieldName($sourceFieldName)
    {
        $this->sourceFieldName = $sourceFieldName;
        return $this;
    }
}
