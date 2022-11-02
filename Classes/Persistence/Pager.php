<?php

namespace Fab\Vidi\Persistence;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * A page browser
 *
 * @author Mario Matzulla <mario@matzullas.de>
 */
class Pager
{
    /**
     * Total amount of entries
     *
     * @var integer
     */
    protected $count;

    /**
     * Current offset
     *
     * @var integer
     */
    protected $offset;

    /**
     * Current page index
     *
     * @var integer
     */
    protected $page;

    /**
     * Number of items per page
     *
     * @var integer
     */
    protected $limit = 10;

    /**
     * Constructs a new Pager
     */
    public function __construct()
    {
        $this->page = 1;
    }

    /**
     * Returns the total amount of entries
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Sets the total amount of entries
     *
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * Returns the current page index
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the current page index
     *
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * Returns the current limit index
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets the current limit index
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return array Items to display
     */
    public function getDisplayItems()
    {
        $last = $this->getLastPage();
        if ($last == 1) {
            return null;
        }
        $values = array();
        for ($i = 1; $i <= $last; $i++) {
            $values[] = array('key' => $i, 'value' => $i);
        }
        return $values;
    }

    /**
     * @return int The last page index
     */
    public function getLastPage()
    {
        $last = intval($this->count / $this->limit);
        if ($this->count % $this->limit > 0) {
            $last++;
        }
        return $last;
    }

    /**
     * @return int The previous page index. Minimum value is 1
     */
    public function getPreviousPage()
    {
        $prev = $this->page - 1;
        if ($prev < 1) {
            $prev = 1;
        }
        return $prev;
    }

    /**
     * @return int The next page index. Maximum valus is the last page
     */
    public function getNextPage()
    {
        $next = $this->page + 1;
        $last = $this->getLastPage();
        if ($next > $last) {
            $next = $last;
        }
        return $next;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
}
