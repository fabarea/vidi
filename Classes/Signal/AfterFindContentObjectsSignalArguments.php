<?php

namespace Fab\Vidi\Signal;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Persistence\Matcher;
use Fab\Vidi\Persistence\Order;

/**
 * Class for storing arguments of a "after find content objects" signal.
 */
class AfterFindContentObjectsSignalArguments
{
    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var array
     */
    protected $contentObjects;

    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var bool
     */
    protected $hasBeenProcessed;

    /**
     * @var int
     */
    protected $numberOfObjects = 0;

    /**
     * @param array $contentObjects
     * @return $this
     */
    public function setContentObjects($contentObjects)
    {
        $this->contentObjects = $contentObjects;
        return $this;
    }

    /**
     * @return array
     */
    public function getContentObjects()
    {
        return $this->contentObjects;
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

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param boolean $hasBeenProcessed
     * @return $this
     */
    public function setHasBeenProcessed($hasBeenProcessed)
    {
        $this->hasBeenProcessed = $hasBeenProcessed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHasBeenProcessed()
    {
        return $this->hasBeenProcessed;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param Matcher $matcher
     * @return $this
     */
    public function setMatcher($matcher)
    {
        $this->matcher = $matcher;
        return $this;
    }

    /**
     * @return Matcher
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param int $numberOfObjects
     * @return $this
     */
    public function setNumberOfObjects($numberOfObjects)
    {
        $this->numberOfObjects = $numberOfObjects;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfObjects()
    {
        return $this->numberOfObjects;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
}
