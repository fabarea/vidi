<?php
namespace TYPO3\CMS\Vidi\Domain\Repository;

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
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;
use TYPO3\CMS\Vidi\Converter\Property;
use TYPO3\CMS\Vidi\DataHandler\ProcessAction;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Persistence\Matcher;
use TYPO3\CMS\Vidi\Persistence\Order;
use TYPO3\CMS\Vidi\Persistence\Query;
use TYPO3\CMS\Vidi\Tca\TcaService;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;

/**
 * Repository for accessing Content
 */
class ContentRepository implements RepositoryInterface {

	/**
	 * Tell whether it is a raw result (array) or object being returned.
	 *
	 * @var bool
	 */
	protected $rawResult = FALSE;

	/**
	 * The data type to be returned, e.g fe_users, fe_groups, tt_content, etc...
	 *
	 * @var string
	 */
	protected $dataType;

	/**
	 * The source field is useful in the context of MM relations to know who is the caller
	 * e.g findByItems which eventually corresponds to a field name.
	 *
	 * @var string
	 */
	protected $sourceFieldName = '';

	/**
	 * @var array
	 */
	protected $errorMessages = array();

	/**
	 * @var QuerySettingsInterface
	 */
	protected $defaultQuerySettings;

	/**
	 * Constructor
	 *
	 * @param string $dataType
	 */
	public function __construct($dataType) {
		$this->dataType = $dataType;
	}

	/**
	 * Returns all objects of this repository.
	 *
	 * @return Content[]
	 */
	public function findAll() {
		$query = $this->createQuery();
		return $query->execute();
	}

	/**
	 * Returns all "distinct" values for a given property.
	 *
	 * @param string $propertyName
	 * @param Matcher $matcher
	 * @return Content[]
	 */
	public function findDistinctValues($propertyName, Matcher $matcher = NULL) {
		$query = $this->createQuery();
		$query->setDistinct($propertyName);

		// Remove empty values from selection.
		$constraint = $query->logicalNot($query->equals($propertyName, ''));

		// Add some additional constraints from the Matcher object.
		$matcherConstraint = NULL;
		if (!is_null($matcher)) {
			$matcherConstraint = $this->computeConstraints($query, $matcher);
		}

		// Assemble the final constraints or not.
		if ($matcherConstraint) {
			$query->logicalAnd($matcherConstraint, $constraint);
			$query->matching($query->logicalAnd($matcherConstraint, $constraint));
		} else {
			$query->matching($constraint);
		}

		return $query->execute();
	}

	/**
	 * Returns all "distinct" values for a given property.
	 *
	 * @param string $propertyName
	 * @param Matcher $matcher
	 * @return int
	 */
	public function countDistinctValues($propertyName, Matcher $matcher = NULL) {
		$query = $this->createQuery();
		$query->setDistinct($propertyName);

		// Remove empty values from selection.
		$constraint = $query->logicalNot($query->equals($propertyName, ''));

		// Add some additional constraints from the Matcher object.
		$matcherConstraint = NULL;
		if (!is_null($matcher)) {
			$matcherConstraint = $this->computeConstraints($query, $matcher);
		}

		// Assemble the final constraints or not.
		if ($matcherConstraint) {
			$query->logicalAnd($matcherConstraint, $constraint);
			$query->matching($query->logicalAnd($matcherConstraint, $constraint));
		} else {
			$query->matching($constraint);
		}

		return $query->count();
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param int $uid The identifier of the object to find
	 * @return Content|NULL
	 * @api
	 */
	public function findByUid($uid) {
		return $this->findByIdentifier($uid);
	}

	/**
	 * Finds all Contents given specified matches.
	 *
	 * @param string $propertyName
	 * @param array $values
	 * @return Content[]
	 */
	public function findIn($propertyName, array $values) {
		$query = $this->createQuery();
		$query->matching($query->in($propertyName, $values));
		return $query->execute();
	}

	/**
	 * Finds all Contents given specified matches.
	 *
	 * @param Matcher $matcher
	 * @param Order $order The order
	 * @param int $limit
	 * @param int $offset
	 * @return Content[]
	 */
	public function findBy(Matcher $matcher, Order $order = NULL, $limit = NULL, $offset = NULL) {

		$query = $this->createQuery();

		if ($limit) {
			$query->setLimit($limit);
		}

		if ($order) {
			$query->setOrderings($order->getOrderings());

			// Loops around the orderings adding if necessary a dummy condition
			// to make sure the relations can be resolved when transforming the query to plain SQL.
			foreach ($order->getOrderings() as $ordering => $direction) {
				if ($this->hasForeignRelationIn($ordering)) {
					$relationalField = $this->getForeignRelationFrom($ordering);
					$matcher->like($relationalField . '.uid', '');
				}
			}
		}

		if ($offset) {
			$query->setOffset($offset);
		}

		$constraints = $this->computeConstraints($query, $matcher);

		if ($constraints) {
			$query->matching($constraints);
		}

		return $query->execute();
	}

	/**
	 * Tell whether the order has a foreign table in its expression, e.g. "metadata.title".
	 *
	 * @param string $ordering
	 * @return bool
	 */
	protected function hasForeignRelationIn($ordering) {
		return strpos($ordering, '.') !== FALSE;
	}

	/**
	 * Extract the foreign relation of the ordering "metadata.title" -> "metadata"
	 *
	 * @param string $ordering
	 * @return string
	 */
	protected function getForeignRelationFrom($ordering) {
		$parts = explode('.', $ordering);
		return $parts[0];
	}

	/**
	 * Find one Content object given specified matches.
	 *
	 * @param Matcher $matcher
	 * @internal param \TYPO3\CMS\Vidi\Persistence\Order $order The order
	 * @internal param int $limit
	 * @internal param int $offset
	 * @return Content
	 */
	public function findOneBy(Matcher $matcher) {

		$query = $this->createQuery();

		$constraints = $this->computeConstraints($query, $matcher);

		if ($constraints) {
			$query->matching($constraints);
		}

		$query->setLimit(1); // only take one!

		$resultSet = $query->execute();
		if ($resultSet) {
			$resultSet = current($resultSet);
		}
		return $resultSet;
	}

	/**
	 * Get the constraints
	 *
	 * @param Query $query
	 * @param Matcher $matcher
	 * @return ConstraintInterface|NULL
	 */
	protected function computeConstraints(Query $query, Matcher $matcher) {

		$constraints = NULL;

		$collectedConstraints = array();

		// Search term
		$constraint = $this->computeSearchTermConstraint($query, $matcher);
		if ($constraint) {
			$collectedConstraints[] = $constraint;
		}

		foreach ($matcher->getSupportedOperators() as $operator) {
			$constraint = $this->computeConstraint($query, $matcher, $operator);
			if ($constraint) {
				$collectedConstraints[] = $constraint;
			}
		}

		if (count($collectedConstraints) > 1) {
			$logical = $matcher->getDefaultLogicalSeparator();
			$constraints = $query->$logical($collectedConstraints);
		} elseif (!empty($collectedConstraints)) {

			// true means there is one constraint only and should become the result
			$constraints = current($collectedConstraints);
		}

		// Trigger signal for post processing the computed constraints object.
		$constraints = $this->emitPostProcessConstraintsSignal($query, $constraints);

		return $constraints;
	}

	/**
	 * Computes the search constraint and returns it.
	 *
	 * @param Query $query
	 * @param Matcher $matcher
	 * @return ConstraintInterface|NULL
	 */
	protected function computeSearchTermConstraint(Query $query, Matcher $matcher) {

		$result = NULL;

		// Search term case
		if ($matcher->getSearchTerm()) {

			$fields = GeneralUtility::trimExplode(',', TcaService::table($this->dataType)->getSearchFields(), TRUE);

			$constraints = array();
			$likeClause = sprintf('%%%s%%', $matcher->getSearchTerm());
			foreach ($fields as $fieldNameAndPath) {
				if ($this->isSuitableForLike($fieldNameAndPath, $matcher->getSearchTerm())) {

					$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $this->dataType);
					$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $this->dataType);

					if (TcaService::table($dataType)->hasField($fieldName) && TcaService::table($dataType)->field($fieldName)->hasRelation()) {
						$foreignTable = TcaService::table($dataType)->field($fieldName)->getForeignTable();
						$fieldNameAndPath = $fieldNameAndPath . '.' . TcaService::table($foreignTable)->getLabelField();
					}
					$constraints[] = $query->like($fieldNameAndPath, $likeClause);
				}
			}
			$logical = $matcher->getLogicalSeparatorForSearchTerm();
			$result = $query->$logical($constraints);
		}

		return $result;
	}

	/**
	 * It does not make sense to have a "like" in presence of numerical field, e.g "uid".
	 * Tell whether the given value makes sense for a "like" clause.
	 *
	 * @param string $fieldNameAndPath
	 * @param string $value
	 * @return bool
	 */
	protected function isSuitableForLike($fieldNameAndPath, $value) {
		$isSuitable = TRUE;

		// TRUE means it is a string
		if (!MathUtility::canBeInterpretedAsInteger($value)) {

			$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $this->dataType);
			$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $this->dataType);

			if (TcaService::table($dataType)->field($fieldName)->isNumerical()
				&& !TcaService::table($dataType)->field($fieldName)->hasRelation()
			) {
				$isSuitable = FALSE;
			}
		}

		return $isSuitable;
	}

	/**
	 * Computes the constraint for matches and returns it.
	 *
	 * @param Query $query
	 * @param Matcher $matcher
	 * @param string $operator
	 * @return ConstraintInterface|NULL
	 */
	protected function computeConstraint(Query $query, Matcher $matcher, $operator) {
		$result = NULL;

		$operatorName = ucfirst($operator);
		$getCriteria = sprintf('get%sCriteria', $operatorName);
		$criteria = $matcher->$getCriteria();

		if (!empty($criteria)) {
			$constraints = array();

			foreach ($criteria as $criterion) {

				$fieldNameAndPath = $criterion['fieldNameAndPath'];
				$operand = $criterion['operand'];

				// Compute a few variables...
				// $dataType is generally equals to $this->dataType but not always... if fieldName is a path.
				$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath, $this->dataType);
				$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath, $this->dataType);
				$fieldPath = $this->getFieldPathResolver()->stripFieldName($fieldNameAndPath, $this->dataType);

				if (TcaService::table($dataType)->field($fieldName)->hasRelation()) {
					if (MathUtility::canBeInterpretedAsInteger($operand)) {
						$fieldNameAndPath = $fieldName . '.uid';
					} else {
						$foreignTableName = TcaService::table($dataType)->field($fieldName)->getForeignTable();
						$foreignTable = TcaService::table($foreignTableName);
						$fieldNameAndPath = $fieldName . '.' . $foreignTable->getLabelField();
					}

					// If different means we should restore the prepended path segment for proper SQL parser.
					// This is TRUE for a composite field, e.g items.sys_file_metadata for categories.
					if ($fieldName !== $fieldPath) {
						$fieldNameAndPath = $fieldPath . '.' . $fieldNameAndPath;
					}
				}

				$constraints[] = $query->$operator($fieldNameAndPath, $criterion['operand']);
			}

			$getLogicalSeparator = sprintf('getLogicalSeparatorFor%s', $operatorName);
			$logical = $matcher->$getLogicalSeparator();
			$result = $query->$logical($constraints);
		}

		return $result;
	}

	/**
	 * Count all Contents given specified matches.
	 *
	 * @param Matcher $matcher
	 * @return int
	 */
	public function countBy(Matcher $matcher) {

		$query = $this->createQuery();

		$constraints = $this->computeConstraints($query, $matcher);

		if ($constraints) {
			$query->matching($constraints);
		}

		return $query->count();
	}

	/**
	 * Update a content with new information.
	 *
	 * @param Content $content
	 * @param $language
	 * @return bool
	 */
	public function localize($content, $language) {

		// Security check
		$this->getContentValidator()->validate($content);
		$this->getLanguageValidator()->validate($language);

		$dataType = $content->getDataType();
		$handler = $this->getDataHandlerFactory()->action(ProcessAction::LOCALIZE)->forType($dataType)->getDataHandler();

		$handlerResult = $handler->processLocalize($content, $language);
		$this->errorMessages = $handler->getErrorMessages();
		return $handlerResult;
	}

	/**
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected function getDataHandler() {
		if (!$this->dataHandler) {
			$this->dataHandler = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		}
		return $this->dataHandler;
	}

	/**
	 * Update a content with new information.
	 *
	 * @param Content $content
	 * @return bool
	 */
	public function update($content) {

		// Security check.
		$this->getContentValidator()->validate($content);

		$dataType = $content->getDataType();
		$handler = $this->getDataHandlerFactory()->action(ProcessAction::UPDATE)->forType($dataType)->getDataHandler();

		$handlerResult = $handler->processUpdate($content);
		$this->errorMessages = $handler->getErrorMessages();
		return $handlerResult;
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param Content $content
	 * @return boolean
	 */
	public function remove($content) {
		$dataType = $content->getDataType();
		$handler = $this->getDataHandlerFactory()->action(ProcessAction::REMOVE)->forType($dataType)->getDataHandler();

		$handlerResult = $handler->processRemove($content);
		$this->errorMessages = $handler->getErrorMessages();
		return $handlerResult;
	}

	/**
	 * Move a content within this repository.
	 *
	 * @param Content $content
	 * @param string $target
	 * @return bool
	 */
	public function move($content, $target) {

		// Security check.
		$this->getContentValidator()->validate($content);

		$dataType = $content->getDataType();
		$handler = $this->getDataHandlerFactory()->action(ProcessAction::MOVE)->forType($dataType)->getDataHandler();

		$handlerResult = $handler->processMove($content, $target);
		$this->errorMessages = $handler->getErrorMessages();
		return $handlerResult;
	}

	/**
	 * Copy a content within this repository.
	 *
	 * @param Content $content
	 * @return bool
	 */
	public function copy($content, $target) {

		// Security check.
		$this->getContentValidator()->validate($content);

		$dataType = $content->getDataType();
		$handler = $this->getDataHandlerFactory()->action(ProcessAction::COPY)->forType($dataType)->getDataHandler();

		$handlerResult = $handler->processCopy($content, $target);
		$this->errorMessages = $handler->getErrorMessages();
		return $handlerResult;
	}

	/**
	 * Dispatches magic methods (findBy[Property]())
	 *
	 * @param string $methodName The name of the magic method
	 * @param string $arguments The arguments of the magic method
	 * @throws UnsupportedMethodException
	 * @return mixed
	 * @api
	 */
	public function __call($methodName, $arguments) {
		if (substr($methodName, 0, 6) === 'findBy' && strlen($methodName) > 7) {
			$propertyName = strtolower(substr(substr($methodName, 6), 0, 1)) . substr(substr($methodName, 6), 1);
			$result = $this->processMagicCall($propertyName, $arguments[0]);
		} elseif (substr($methodName, 0, 9) === 'findOneBy' && strlen($methodName) > 10) {
			$propertyName = strtolower(substr(substr($methodName, 9), 0, 1)) . substr(substr($methodName, 9), 1);
			$result = $this->processMagicCall($propertyName, $arguments[0], 'one');
		} elseif (substr($methodName, 0, 7) === 'countBy' && strlen($methodName) > 8) {
			$propertyName = strtolower(substr(substr($methodName, 7), 0, 1)) . substr(substr($methodName, 7), 1);
			$result = $this->processMagicCall($propertyName, $arguments[0], 'count');
		} else {
			throw new UnsupportedMethodException('The method "' . $methodName . '" is not supported by the repository.', 1360838010);
		}
		return $result;
	}

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return Query
	 * @api
	 */
	public function createQuery() {
		/** @var Query $query */
		$query = $this->getObjectManager()->get('TYPO3\CMS\Vidi\Persistence\Query', $this->dataType);
		$query->setSourceFieldName($this->sourceFieldName);

		if ($this->defaultQuerySettings) {
			$query->setQuerySettings($this->defaultQuerySettings);
		} else {

			// Initialize and pass the query settings at this level.
			/** @var \TYPO3\CMS\Vidi\Persistence\QuerySettings $querySettings */
			$querySettings = $this->getObjectManager()->get('TYPO3\CMS\Vidi\Persistence\QuerySettings');

			// Default choice for the BE.
			if ($this->isBackendMode()) {
				$querySettings->setIgnoreEnableFields(TRUE);
			}

			$query->setQuerySettings($querySettings);
		}

		return $query;
	}

	/**
	 * @return boolean
	 */
	public function getRawResult() {
		return $this->rawResult;
	}

	/**
	 * @param boolean $rawResult
	 * @return \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository
	 */
	public function setRawResult($rawResult) {
		$this->rawResult = $rawResult;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * Handle the magic call by properly creating a Query object and returning its result.
	 *
	 * @param string $propertyName
	 * @param string $value
	 * @param string $flag
	 * @return array
	 */
	protected function processMagicCall($propertyName, $value, $flag = '') {

		$fieldName = Property::name($propertyName)->of($this->dataType)->toFieldName();

		/** @var $matcher Matcher */
		$matcher = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Matcher', array(), $this->getDataType());

		$table = TcaService::table($this->dataType);
		if ($table->field($fieldName)->isGroup()) {

			$valueParts = explode('.', $value, 2);
			$fieldName = $fieldName . '.' . $valueParts[0];
			$value = $valueParts[1];
		}

		$matcher->equals($fieldName, $value);

		if ($flag == 'count') {
			$result = $this->countBy($matcher);
		} else {
			$result = $this->findBy($matcher);
		}
		return $flag == 'one' && !empty($result) ? reset($result) : $result;
	}

	/**
	 * Adds an object to this repository.
	 *
	 * @param object $object The object to add
	 * @throws \BadMethodCallException
	 * @return void
	 * @api
	 */
	public function add($object) {
		throw new \BadMethodCallException('Repository does not support the add() method.', 1375805599);
	}

	/**
	 * Returns the total number objects of this repository.
	 *
	 * @return integer The object count
	 * @api
	 */
	public function countAll() {
		$query = $this->createQuery();
		return $query->count();
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll() {
		// TODO: Implement removeAll() method.
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param mixed $identifier The identifier of the object to find
	 * @return Content|NULL
	 * @api
	 */
	public function findByIdentifier($identifier) {
		$query = $this->createQuery();

		$result = $query->matching($query->equals('uid', $identifier))
			->execute();

		if (is_array($result)) {
			$result = current($result);
		}

		return $result;
	}

	/**
	 * Sets the property names to order the result by per default.
	 * Expected like this:
	 * array(
	 * 'foo' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
	 * 'bar' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @param array $defaultOrderings The property names to order by
	 * @throws \BadMethodCallException
	 * @return void
	 * @api
	 */
	public function setDefaultOrderings(array $defaultOrderings) {
		throw new \BadMethodCallException('Repository does not support the setDefaultOrderings() method.', 1375805598);
	}

	/**
	 * Sets the default query settings to be used in this repository
	 *
	 * @param QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
	 * @throws \BadMethodCallException
	 * @return void
	 * @api
	 */
	public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings) {
		$this->defaultQuerySettings = $defaultQuerySettings;
	}

	/**
	 * @return array
	 */
	public function getErrorMessages() {
		return $this->errorMessages;
	}

	/**
	 * @param string $sourceFieldName
	 * @return $this
	 */
	public function setSourceFieldName($sourceFieldName) {
		$this->sourceFieldName = $sourceFieldName;
		return $this;
	}

	/**
	 * @return \TYPO3\CMS\Vidi\DataHandler\DataHandlerFactory
	 */
	protected function getDataHandlerFactory() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\DataHandler\DataHandlerFactory');
	}

	/**
	 * Returns whether the current mode is Backend
	 *
	 * @return bool
	 */
	protected function isBackendMode() {
		return TYPO3_MODE == 'BE';
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Domain\Validator\ContentValidator
	 */
	protected function getContentValidator() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Domain\Validator\ContentValidator');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Domain\Validator\LanguageValidator
	 */
	protected function getLanguageValidator() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Domain\Validator\LanguageValidator');
	}

	/**
	 * Signal that is called for post-processing the computed constraints object.
	 *
	 * @param Query $query
	 * @param ConstraintInterface|NULL $constraints
	 * @return ConstraintInterface|NULL $constraints
	 * @signal
	 */
	protected function emitPostProcessConstraintsSignal(Query $query, $constraints) {
		$result = $this->getSignalSlotDispatcher()->dispatch(
			'TYPO3\CMS\Vidi\Domain\Repository\ContentRepository',
			'postProcessConstraintsObject',
			array(
				$query,
				$constraints
			)
		);

		return $result[1];
	}

	/**
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		return $this->getObjectManager()->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	}

}
