<?php
namespace TYPO3\CMS\Vidi;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Factory class to server instances related persistence object.
 */
class PersistenceObjectFactory implements \TYPO3\CMS\Core\SingletonInterface{

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Gets a singleton instance of this class.
	 *
	 * @return \TYPO3\CMS\Vidi\PersistenceObjectFactory
	 */
	static public function getInstance() {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		return $objectManager->get('TYPO3\CMS\Vidi\PersistenceObjectFactory');
	}

	/**
	 * Returns a matcher object.
	 *
	 * @param string $dataType
	 * @return \TYPO3\CMS\Vidi\Persistence\Matcher
	 */
	public function getMatcherObject($dataType = '') {

		/** @var $matcher \TYPO3\CMS\Vidi\Persistence\Matcher */
		$matcher = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Matcher', array(), $dataType);

		// Special case for Grid in the BE using jQuery DataTables plugin.
		// Retrieve a possible search term from GP.
		$searchTerm = GeneralUtility::_GP('sSearch');

		if (strlen($searchTerm) > 0) {

			$tcaTableService = TcaService::table($dataType);

			// try to parse a json query
			$terms = json_decode($searchTerm, TRUE);
			if (is_array($terms)) {

				foreach ($terms as $term) {
					$fieldName = key($term);
					$value = current($term);
					if ($fieldName === 'text') {
						$matcher->setSearchTerm($value);
					} elseif (($tcaTableService->field($fieldName)->hasRelation() && is_numeric($value))
						|| $tcaTableService->field($fieldName)->isNumerical()
					) {
						$matcher->equals($fieldName, $value);
					} else {
						$matcher->likes($fieldName, $value);
					}
				}
			} else {
				$matcher->setSearchTerm($searchTerm);
			}
		}

		// Trigger signal for post processing Matcher Object.
		$this->emitPostProcessMatcherObjectSignal($matcher);

		return $matcher;
	}

	/**
	 * Returns an order object.
	 *
	 * @param string $dataType
	 * @return \TYPO3\CMS\Vidi\Persistence\Order
	 */
	public function getOrderObject($dataType = '') {

		// Default ordering
		$order = Tca\TcaService::table($dataType)->getDefaultOrderings();

		// Retrieve a possible id of the column from the request
		$columnPosition = GeneralUtility::_GP('iSortCol_0');
		if ($columnPosition > 0) {
			$field = Tca\TcaService::grid()->getFieldNameByPosition($columnPosition);

			$direction = GeneralUtility::_GP('sSortDir_0');
			$order = array(
				$field => strtoupper($direction)
			);
		}
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Order', $order);
	}

	/**
	 * Returns a pager object.
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\Pager
	 */
	public function getPagerObject() {

		/** @var $pager \TYPO3\CMS\Vidi\Persistence\Pager */
		$pager = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Pager');

		// Set items per page
		if (GeneralUtility::_GET('iDisplayLength') !== NULL) {
			$limit = (int) GeneralUtility::_GET('iDisplayLength');
			$pager->setLimit($limit);
		}

		// Set offset
		$offset = 0;
		if (GeneralUtility::_GET('iDisplayStart') !== NULL) {
			$offset = (int) GeneralUtility::_GET('iDisplayStart');
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

	/**
	 * Signal that is called for post-processing a matcher object.
	 *
	 * @param \TYPO3\CMS\Vidi\Persistence\Matcher $matcher
	 * @signal
	 */
	protected function emitPostProcessMatcherObjectSignal(\TYPO3\CMS\Vidi\Persistence\Matcher $matcher) {

		if (strlen($matcher->getDataType()) <= 0) {

			/** @var ModuleLoader $moduleLoader */
			$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\ModuleLoader');
			$matcher->setDataType($moduleLoader->getDataType());
		}

		$this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\Controller\Backend\ContentController', 'postProcessMatcherObject', array($matcher, $matcher->getDataType()));
	}

	/**
	 * Get the SignalSlot dispatcher
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		return $this->objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	}

}
