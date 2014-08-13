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
