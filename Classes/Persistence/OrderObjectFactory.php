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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Factory class related to Order object.
 */
class OrderObjectFactory implements SingletonInterface {

	/**
	 * Gets a singleton instance of this class.
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\OrderObjectFactory
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\OrderObjectFactory');
	}

	/**
	 * Returns an order object.
	 *
	 * @param string $dataType
	 * @return \TYPO3\CMS\Vidi\Persistence\Order
	 */
	public function getOrder($dataType = '') {

		// Default ordering
		$order = TcaService::table($dataType)->getDefaultOrderings();

		// Retrieve a possible id of the column from the request
		$columnPosition = GeneralUtility::_GP('iSortCol_0');
		if ($columnPosition > 0) {
			$field = TcaService::grid()->getFieldNameByPosition($columnPosition);

			$direction = GeneralUtility::_GP('sSortDir_0');
			$order = array(
				$field => strtoupper($direction)
			);
		}
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Order', $order);
	}
}
