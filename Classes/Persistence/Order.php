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
 * Order class for order that will apply to a query
 */
class Order  {

	/**
	 * The orderings
	 *
	 * @var array
	 */
	protected $orderings = array();

	/**
	 * Constructs a new Order
	 *
	 * @para array $orders
	 */
	public function __construct($orders = array()) {
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
	public function addOrdering($order, $direction) {
		$this->orderings[$order] = $direction;
	}

	/**
	 * Returns the order
	 *
	 * @return array The order
	 */
	public function getOrderings() {
		return $this->orderings;
	}
}
