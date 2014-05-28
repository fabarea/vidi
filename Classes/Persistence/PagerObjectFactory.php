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

/**
 * Factory class related to Pager object.
 */
class PagerObjectFactory implements SingletonInterface {

	/**
	 * Gets a singleton instance of this class.
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\PagerObjectFactory
	 */
	static public function getInstance() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\PagerObjectFactory');
	}

	/**
	 * Returns a pager object.
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\Pager
	 */
	public function getPager() {

		/** @var $pager \TYPO3\CMS\Vidi\Persistence\Pager */
		$pager = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Pager');

		// Set items per page
		if (GeneralUtility::_GET('iDisplayLength') !== NULL) {
			$limit = (int)GeneralUtility::_GET('iDisplayLength');
			$pager->setLimit($limit);
		}

		// Set offset
		$offset = 0;
		if (GeneralUtility::_GET('iDisplayStart') !== NULL) {
			$offset = (int)GeneralUtility::_GET('iDisplayStart');
		}
		$pager->setOffset($offset);

		// set page
		$page = 1;
		if ($pager->getLimit() > 0) {
			$page = round($pager->getOffset() / $pager->getLimit());
		}
		$pager->setPage($page);

		return $pager;
	}

}
