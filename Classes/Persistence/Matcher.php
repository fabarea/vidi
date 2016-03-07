<?php
namespace Fab\Vidi\Persistence;

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
class Matcher
{

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
    protected $supportedOperators = [
        '=' => 'equals',
        'in' => 'in',
        'like' => 'like',
        '>' => 'greaterThan',
        '>=' => 'greaterThanOrEqual',
        '<' => 'lessThan',
        '<=' => 'lessThanOrEqual'
    ];

    /**
     * @var array
     */
    protected $equals = [];

    /**
     * @var array
     */
    protected $greaterThan = [];

    /**
     * @var array
     */
    protected $greaterThanOrEqual = [];

    /**
     * @var array
     */
    protected $lessThan = [];

    /**
     * @var array
     */
    protected $lessThanOrEqual = [];

    /**
     * @var array
     */
    protected $in = [];

    /**
     * @var array
     */
    protected $like = [];

    /**
     * @var string
     */
    protected $defaultLogicalSeparator = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForEquals = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForGreaterThan = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForGreaterThanOrEqual = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForLessThan = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForLessThanOrEqual = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForIn = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForLike = self::LOGICAL_AND;

    /**
     * @var string
     */
    protected $logicalSeparatorForSearchTerm = self::LOGICAL_OR;

    /**
     * Constructs a new Matcher
     *
     * @param array $matches associative [$field => $value]
     * @param string $dataType which corresponds to an entry of the TCA (table name).
     * @return \Fab\Vidi\Persistence\Matcher
     */
    public function __construct($matches = [], $dataType = '')
    {
        $this->dataType = $dataType;
        $this->matches = $matches;
    }

    /**
     * @param string $searchTerm
     * @return \Fab\Vidi\Persistence\Matcher
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @return array
     */
    public function getEquals()
    {
        return $this->equals;
    }

    /**
     * @param $fieldNameAndPath
     * @param $operand
     * @return $this
     */
    public function equals($fieldNameAndPath, $operand)
    {
        $this->equals[] = ['fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand];
        return $this;
    }

    /**
     * @return array
     */
    public function getGreaterThan()
    {
        return $this->greaterThan;
    }

    /**
     * @param $fieldNameAndPath
     * @param $operand
     * @return $this
     */
    public function greaterThan($fieldNameAndPath, $operand)
    {
        $this->greaterThan[] = ['fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand];
        return $this;
    }

    /**
     * @return array
     */
    public function getGreaterThanOrEqual()
    {
        return $this->greaterThanOrEqual;
    }

    /**
     * @param $fieldNameAndPath
     * @param $operand
     * @return $this
     */
    public function greaterThanOrEqual($fieldNameAndPath, $operand)
    {
        $this->greaterThanOrEqual[] = ['fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand];
        return $this;
    }

    /**
     * @return array
     */
    public function getLessThan()
    {
        return $this->lessThan;
    }

    /**
     * @param $fieldNameAndPath
     * @param $operand
     * @return $this
     */
    public function lessThan($fieldNameAndPath, $operand)
    {
        $this->lessThan[] = ['fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand];
        return $this;
    }

    /**
     * @return array
     */
    public function getLessThanOrEqual()
    {
        return $this->lessThanOrEqual;
    }

    /**
     * @param $fieldNameAndPath
     * @param $operand
     * @return $this
     */
    public function lessThanOrEqual($fieldNameAndPath, $operand)
    {
        $this->lessThanOrEqual[] = ['fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand];
        return $this;
    }

    /**
     * @return array
     */
    public function getLike()
    {
        return $this->like;
    }

    /**
     * @param $fieldNameAndPath
     * @param $operand
     * @return $this
     */
    public function in($fieldNameAndPath, $operand)
    {
        $this->in[] = ['fieldNameAndPath' => $fieldNameAndPath, 'operand' => $operand];
        return $this;
    }

    /**
     * @return array
     */
    public function getIn()
    {
        return $this->in;
    }

    /**
     * @param $fieldNameAndPath
     * @param $operand
     * @param bool $addWildCard
     * @return $this
     */
    public function like($fieldNameAndPath, $operand, $addWildCard = TRUE)
    {
        $wildCardSymbol = $addWildCard ? '%' : '';
        $this->like[] = ['fieldNameAndPath' => $fieldNameAndPath, 'operand' => $wildCardSymbol . $operand . $wildCardSymbol];
        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultLogicalSeparator()
    {
        return $this->defaultLogicalSeparator;
    }

    /**
     * @param string $defaultLogicalSeparator
     * @return $this
     */
    public function setDefaultLogicalSeparator($defaultLogicalSeparator)
    {
        $this->defaultLogicalSeparator = $defaultLogicalSeparator;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForEquals()
    {
        return $this->logicalSeparatorForEquals;
    }

    /**
     * @param string $logicalSeparatorForEquals
     * @return $this
     */
    public function setLogicalSeparatorForEquals($logicalSeparatorForEquals)
    {
        $this->logicalSeparatorForEquals = $logicalSeparatorForEquals;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForGreaterThan()
    {
        return $this->logicalSeparatorForGreaterThan;
    }

    /**
     * @param string $logicalSeparatorForGreaterThan
     * @return $this
     */
    public function setLogicalSeparatorForGreaterThan($logicalSeparatorForGreaterThan)
    {
        $this->logicalSeparatorForGreaterThan = $logicalSeparatorForGreaterThan;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForGreaterThanOrEqual()
    {
        return $this->logicalSeparatorForGreaterThanOrEqual;
    }

    /**
     * @param string $logicalSeparatorForGreaterThanOrEqual
     * @return $this
     */
    public function setLogicalSeparatorForGreaterThanOrEqual($logicalSeparatorForGreaterThanOrEqual)
    {
        $this->logicalSeparatorForGreaterThanOrEqual = $logicalSeparatorForGreaterThanOrEqual;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForLessThan()
    {
        return $this->logicalSeparatorForLessThan;
    }

    /**
     * @param string $logicalSeparatorForLessThan
     * @return $this
     */
    public function setLogicalSeparatorForLessThan($logicalSeparatorForLessThan)
    {
        $this->logicalSeparatorForLessThan = $logicalSeparatorForLessThan;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForLessThanOrEqual()
    {
        return $this->logicalSeparatorForLessThanOrEqual;
    }

    /**
     * @param string $logicalSeparatorForLessThanOrEqual
     * @return $this
     */
    public function setLogicalSeparatorForLessThanOrEqual($logicalSeparatorForLessThanOrEqual)
    {
        $this->logicalSeparatorForLessThanOrEqual = $logicalSeparatorForLessThanOrEqual;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForIn()
    {
        return $this->logicalSeparatorForIn;
    }

    /**
     * @param string $logicalSeparatorForIn
     * @return $this
     */
    public function setLogicalSeparatorForIn($logicalSeparatorForIn)
    {
        $this->logicalSeparatorForIn = $logicalSeparatorForIn;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForLike()
    {
        return $this->logicalSeparatorForLike;
    }

    /**
     * @param string $logicalSeparatorForLike
     * @return $this
     */
    public function setLogicalSeparatorForLike($logicalSeparatorForLike)
    {
        $this->logicalSeparatorForLike = $logicalSeparatorForLike;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalSeparatorForSearchTerm()
    {
        return $this->logicalSeparatorForSearchTerm;
    }

    /**
     * @param string $logicalSeparatorForSearchTerm
     * @return $this
     */
    public function setLogicalSeparatorForSearchTerm($logicalSeparatorForSearchTerm)
    {
        $this->logicalSeparatorForSearchTerm = $logicalSeparatorForSearchTerm;
        return $this;
    }

    /**
     * @return array
     */
    public function getSupportedOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     * @return $this
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        return $this;
    }
}
