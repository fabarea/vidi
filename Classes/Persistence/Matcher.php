<?php
namespace TYPO3\CMS\Vidi\Persistence;

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

/**
 * Matcher class for conditions that will apply to a query.
 */
class Matcher {

	/**
	 * The logical OR
	 */
	const LOGICAL_OR = 'logicalOr';

	/**
	 * The logical AND
	 */
	const LOGICAL_AND = 'logicalAnd';

	/**
	 * @var string
	 */
	protected $dataType = '';

	/**
	 * @var string
	 */
	protected $searchTerm = '';

	/**
	 * @var array
	 */
	protected $supportedOperators = array('equals', 'in', 'like');

	/**
	 * Associative values used for "equals" operator ($fieldName => $value)
	 *
	 * @var array
	 */
	protected $equalsCriteria = array();

	/**
	 * Associative values used for "in" operator ($fieldName => $value)
	 *
	 * @var array
	 */
	protected $inCriteria = array();

	/**
	 * Associative values used for "like" operator ($fieldName => $value)
	 *
	 * @var array
	 */
	protected $likeCriteria = array();

	/**
	 * Default logical operator for like.
	 *
	 * @var string
	 */
	protected $defaultLogicalSeparator = self::LOGICAL_AND;

	/**
	 * Default logical operator for equals.
	 *
	 * @var string
	 */
	protected $logicalSeparatorForEquals = self::LOGICAL_AND;

	/**
	 * Default logical operator for equals.
	 *
	 * @var string
	 */
	protected $logicalSeparatorForIn = self::LOGICAL_AND;

	/**
	 * Default logical operator for like.
	 *
	 * @var string
	 */
	protected $logicalSeparatorForLike = self::LOGICAL_AND;

	/**
	 * Default logical operator for the search term.
	 *
	 * @var string
	 */
	protected $logicalSeparatorForSearchTerm = self::LOGICAL_OR;

	/**
	 * Constructs a new Matcher
	 *
	 * @param array $matches associative array($field => $value)
	 * @param string $dataType which corresponds to an entry of the TCA (table name).
	 * @return \TYPO3\CMS\Vidi\Persistence\Matcher
	 */
	public function __construct($matches = array(), $dataType = '') {
		$this->dataType = $dataType;
		$this->matches = $matches;
	}

	/**
	 * @param string $searchTerm
	 * @return \TYPO3\CMS\Vidi\Persistence\Matcher
	 */
	public function setSearchTerm($searchTerm) {
		$this->searchTerm = $searchTerm;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSearchTerm() {
		return $this->searchTerm;
	}

	/**
	 * @return array
	 */
	public function getEqualsCriteria() {
		return $this->equalsCriteria;
	}

	/**
	 * @param $fieldNameAndPath
	 * @param $operand
	 * @return $this
	 */
	public function equals($fieldNameAndPath, $operand) {
		$this->equalsCriteria[] = array('fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLikeCriteria() {
		return $this->likeCriteria;
	}

	/**
	 * @param $fieldNameAndPath
	 * @param $operand
	 * @return $this
	 */
	public function in($fieldNameAndPath, $operand) {
		$this->inCriteria[] = array('fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getInCriteria() {
		return $this->inCriteria;
	}

	/**
	 * @param $fieldNameAndPath
	 * @param $operand
	 * @return $this
	 */
	public function like($fieldNameAndPath, $operand) {
		$this->likeCriteria[] = array('fieldNameAndPath' => $fieldNameAndPath, 'operand' => '%' . $operand . '%');
		return $this;
	}

	/**
	 * @param $fieldNameAndPath
	 * @param $operand
	 * @return $this
	 * @deprecated Use method "like" instead which is inline with the Query Interface. Will be removed in 0.7 + 2 versions.
	 */
	public function likes($fieldNameAndPath, $operand) {
		return $this->like($fieldNameAndPath, $operand);
	}

	/**
	 * @return array
	 */
	public function getDefaultLogicalSeparator() {
		return $this->defaultLogicalSeparator;
	}

	/**
	 * @param string $defaultLogicalSeparator
	 * @return $this
	 */
	public function setDefaultLogicalSeparator($defaultLogicalSeparator) {
		$this->defaultLogicalSeparator = $defaultLogicalSeparator;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogicalSeparatorForEquals() {
		return $this->logicalSeparatorForEquals;
	}

	/**
	 * @param string $logicalSeparatorForEquals
	 * @return $this
	 */
	public function setLogicalSeparatorForEquals($logicalSeparatorForEquals) {
		$this->logicalSeparatorForEquals = $logicalSeparatorForEquals;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogicalSeparatorForIn() {
		return $this->logicalSeparatorForIn;
	}

	/**
	 * @param string $logicalSeparatorForIn
	 * @return $this
	 */
	public function setLogicalSeparatorForIn($logicalSeparatorForIn) {
		$this->logicalSeparatorForIn = $logicalSeparatorForIn;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogicalSeparatorForLike() {
		return $this->logicalSeparatorForLike;
	}

	/**
	 * @param string $logicalSeparatorForLike
	 * @return $this
	 */
	public function setLogicalSeparatorForLike($logicalSeparatorForLike) {
		$this->logicalSeparatorForLike = $logicalSeparatorForLike;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogicalSeparatorForSearchTerm() {
		return $this->logicalSeparatorForSearchTerm;
	}

	/**
	 * @param string $logicalSeparatorForSearchTerm
	 * @return $this
	 */
	public function setLogicalSeparatorForSearchTerm($logicalSeparatorForSearchTerm) {
		$this->logicalSeparatorForSearchTerm = $logicalSeparatorForSearchTerm;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSupportedOperators() {
		return $this->supportedOperators;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * @param string $dataType
	 * @return $this
	 */
	public function setDataType($dataType) {
		$this->dataType = $dataType;
		return $this;
	}
}
