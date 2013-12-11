<?php
namespace TYPO3\CMS\Vidi\Persistence;

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
	protected $supportedOperators = array('equals', 'like');

	/**
	 * Associative values used for equals operator ($fieldName => $value)
	 *
	 * @var array
	 */
	protected $equalsCriteria = array();

	/**
	 * Associative values used for like operator ($fieldName => $value)
	 *
	 * @var array
	 */
	protected $likeCriteria = array();

	/**
	 * Default logical operator for like.
	 *
	 * @var array
	 */
	protected $defaultLogicalSeparator = self::LOGICAL_AND;

	/**
	 * Default logical operator for like.
	 *
	 * @var array
	 */
	protected $logicalSeparatorForLike = self::LOGICAL_AND;


	/**
	 * Default logical operator for equals.
	 *
	 * @var array
	 */
	protected $logicalSeparatorForEquals = self::LOGICAL_AND;

	/**
	 * Default logical operator for the search term.
	 *
	 * @var array
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
	 * @param $propertyName
	 * @param $operand
	 * @return $this
	 */
	public function equals($propertyName, $operand) {
		$this->equalsCriteria[] = array('propertyName' => $propertyName, 'operand' => $operand);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLikeCriteria() {
		return $this->likeCriteria;
	}

	/**
	 * @param $propertyName
	 * @param $operand
	 * @return $this
	 */
	public function likes($propertyName, $operand) {
		$this->likeCriteria[] = array('propertyName' => $propertyName, 'operand' => '%' . $operand . '%');
		return $this;
	}

	/**
	 * @return array
	 */
	public function getDefaultLogicalSeparator() {
		return $this->defaultLogicalSeparator;
	}

	/**
	 * @param array $defaultLogicalSeparator
	 * @return $this
	 */
	public function setDefaultLogicalSeparator($defaultLogicalSeparator) {
		$this->defaultLogicalSeparator = $defaultLogicalSeparator;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLogicalSeparatorForLike() {
		return $this->logicalSeparatorForLike;
	}

	/**
	 * @param array $logicalSeparatorForLike
	 * @return $this
	 */
	public function setLogicalSeparatorForLike($logicalSeparatorForLike) {
		$this->logicalSeparatorForLike = $logicalSeparatorForLike;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLogicalSeparatorForEquals() {
		return $this->logicalSeparatorForEquals;
	}

	/**
	 * @param array $logicalSeparatorForEquals
	 * @return $this
	 */
	public function setLogicalSeparatorForEquals($logicalSeparatorForEquals) {
		$this->logicalSeparatorForEquals = $logicalSeparatorForEquals;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getLogicalSeparatorForSearchTerm() {
		return $this->logicalSeparatorForSearchTerm;
	}

	/**
	 * @param array $logicalSeparatorForSearchTerm
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

?>