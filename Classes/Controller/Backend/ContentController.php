<?php
namespace TYPO3\CMS\Vidi\Controller\Backend;
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
 * Controller which handles actions related to Vidi in the Backend.
 */
class ContentController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 * @inject
	 */
	protected $pageRenderer;

	/**
	 * Initialize every action.
	 */
	public function initializeAction() {
		$this->pageRenderer->addInlineLanguageLabelFile('EXT:vidi/Resources/Private/Language/locallang.xlf');
	}

	/**
	 * List action for this controller.
	 *
	 * @return void
	 */
	public function listAction() {
		$this->view->assign('columns', \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService()->getFields());
	}

	/**
	 * List Row action for this controller. Output a json list of contents
	 * This action is expected to have a parameter format = json
	 *
	 * @param array $matches
	 * @return void
	 */
	public function listRowAction($matches = array()) {

		// Initialize some objects related to the query
		$matcherObject = $this->createMatcherObject();
		foreach ($matches as $propertyName => $value) {
			$matcherObject->addMatch($propertyName, $value);
		}

		$orderObject = $this->createOrderObject();
		$pagerObject = $this->createPagerObject();

		// Fetch the adequate repository
		$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance();

		// Query the repository
		$contents = $contentRepository->findBy($matcherObject, $orderObject, $pagerObject->getLimit(), $pagerObject->getOffset());
		$numberOfContents = $contentRepository->countBy($matcherObject);
		$pagerObject->setCount($numberOfContents);

		// Assign values
		$this->view->assign('contents', $contents);
		$this->view->assign('numberOfContents', $numberOfContents);
		$this->view->assign('pager', $pagerObject);

		$this->request->setFormat('json');
		# Json header is not automatically respected in the BE... so send one the hard way.
		header('Content-type: application/json');
	}

	/**
	 * @param array $content
	 * @throws \TYPO3\CMS\Vidi\Exception\MissingUidException
	 * @return void
	 * @dontvalidate $content
	 */
	public function updateAction(array $content = array()) {

		if (empty($content['uid'])) {
			throw new \TYPO3\CMS\Vidi\Exception\MissingUidException('Missing Uid', 1351605545);
		}

		/** @var \TYPO3\CMS\Vidi\ModuleLoader $moduleLoader */
		$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\ModuleLoader');

		// transform array given as argument to object.
		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $content */
		$contentObject = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Model\Content',
			$moduleLoader->getDataType(),
			$content
		);

		// Fetch the adequate repository
		$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance();
		$contentRepository->update($contentObject);

		// reload object from repository
		$contentObject = $contentRepository->findByUid($contentObject->getUid());

		// extract keys of content.
		$keys = array_keys($content);

		// assuming the field name is the first parameter of content
		$fieldName = array_shift($keys);
		return $contentObject[$fieldName];
	}

	/**
	 * Delete a row given an object uid.
	 * This action is expected to have a parameter format = json
	 *
	 * @param int $content
	 * @return string
	 */
	public function deleteAction($content) {

		// Fetch the adequate repository
		$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance();

		$labelField = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getTableService()->getLabelField();
		$getter = 'get' . ucfirst($labelField);

		$contentObject = $contentRepository->findByUid($content);
		$result['status'] = $contentRepository->remove($contentObject);
		$result['action'] = 'delete';
		if ($result['status']) {
			$result['object'] = array(
				'uid' => $contentObject->getUid(),
				$labelField => $contentObject->$getter(),
			);
		}

		# Json header is not automatically respected in the BE... so send one the hard way.
		header('Content-type: application/json');
		return json_encode($result);
	}

	/**
	 * Mass delete objects
	 * This action is expected to have a parameter format = json
	 *
	 * @param array $contents
	 * @return string
	 */
	public function massDeleteAction($contents) {

		foreach ($contents as $content) {
			$result = $this->deleteAction($content);
		}

		# Json header is not automatically respected in the BE... so send one the hard way.
		header('Content-type: application/json');
		return json_encode($result);
	}

	/**
	 * Returns a matcher object.
	 * Note: this code is very much tight to the BE module. It should / could probably be improved at one point...
	 *
	 * @return \TYPO3\CMS\Vidi\QueryElement\Matcher
	 */
	protected function createMatcherObject() {

		/** @var $matcher \TYPO3\CMS\Vidi\QueryElement\Matcher */
		$matcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\QueryElement\Matcher');

		// Special case for Grid in the BE using jQuery DataTables plugin.
		// Retrieve a possible search term from GP.
		$searchTerm = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sSearch');
		if (strlen($searchTerm) > 0) {
			$terms = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(':', $searchTerm);

			if (count($terms) == 2) {
				$fieldName = $terms[0];
				$valueToMatch = $terms[1];

				$tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService();
				$fields = $tcaFieldService->getFieldNames();
				foreach (array('uid', 'pid') as $field) {
					$fields[] = $field;
				}

				if (in_array($fieldName, $fields) && strlen($valueToMatch) > 0) {
					$matcher->setMatches(array($fieldName => $valueToMatch));
				} else {
					// must be empty because field name is invalid.
					$matcher->setMatches(array('uid' => -1));
				}
			} else {
				$matcher->setSearchTerm($searchTerm);
			}
		}

		return $matcher;
	}

	/**
	 * Returns an order object.
	 * Note: this code is very much tight to the BE module. It should / could probably be improved at one point...
	 *
	 * @return \TYPO3\CMS\Vidi\QueryElement\Order
	 */
	protected function createOrderObject() {
		// Default sort
		$order['uid'] = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;

		// Retrieve a possible id of the column from the request
		$columnPosition = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('iSortCol_0');
		if ($columnPosition > 0) {
			$field = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService()->getFieldNameByPosition($columnPosition);

			$direction = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sSortDir_0');
			$order = array(
				$field => strtoupper($direction)
			);
		}
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\QueryElement\Order', $order);
	}

	/**
	 * Returns a pager object.
	 * Note: this code is very much tight to the BE module. It should / could probably be improved at one point...
	 *
	 * @return \TYPO3\CMS\Vidi\QueryElement\Pager
	 */
	protected function createPagerObject() {

		/** @var $pager \TYPO3\CMS\Vidi\QueryElement\Pager */
		$pager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\QueryElement\Pager');

		// Set items per page
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('iDisplayLength')) {
			$limit = (int) \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('iDisplayLength');
			$pager->setLimit($limit);
		}

		// Set offset
		$offset = 0;
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('iDisplayStart')) {
			$offset = (int) \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('iDisplayStart');
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
?>
