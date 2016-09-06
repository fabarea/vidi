<?php
namespace Fab\Vidi\Persistence;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Order class for order that will apply to a query
 */
class Order
{

    /**
     * The orderings
     *
     * @var array
     */
    protected $orderings = [];

    /**
     * Constructs a new Order
     *
     * @para array $orders
     * @param array $orders
     */
    public function __construct($orders = array())
    {
        foreach ($orders as $order => $direction) {
            $this->addOrdering($order, $direction);
        }
    }

    /**
     * Add ordering
     *
     * @param string $order The order
     * @param string $direction ASC / DESC
     * @return void
     */
    public function addOrdering($order, $direction)
    {
        $this->orderings[$order] = $direction;
    }

    /**
     * Returns the order
     *
     * @return array The order
     */
    public function getOrderings()
    {
        return $this->orderings;
    }
}
