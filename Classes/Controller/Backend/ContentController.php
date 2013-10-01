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
use TYPO3\CMS\Vidi\ModuleLoader;

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
	 * @var \TYPO3\CMS\Vidi\ViewHelperRenderer
	 * @inject
	 */
	protected $viewHelperRenderer;

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

		/** @var ModuleLoader $moduleLoader */
		$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\ModuleLoader');

		$viewHelpers = $moduleLoader->getHeaderComponents(ModuleLoader::TOP, ModuleLoader::LEFT);
		$this->view->assign('headerTopLeftComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getHeaderComponents(ModuleLoader::TOP, ModuleLoader::RIGHT);
		$this->view->assign('headerTopRightComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getHeaderComponents(ModuleLoader::BOTTOM, ModuleLoader::LEFT);
		$this->view->assign('headerBottomLeftComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getHeaderComponents(ModuleLoader::BOTTOM, ModuleLoader::RIGHT);
		$this->view->assign('headerBottomRightComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getBodyComponents(ModuleLoader::TOP);
		$this->view->assign('bodyTopComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getBodyComponents(ModuleLoader::BOTTOM);
		$this->view->assign('bodyBottomComponents', $this->viewHelperRenderer->render($viewHelpers));

		$this->view->assign('columns', \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getGridService()->getFields());
	}

	/**
	 * List Row action for this controller. Output a json list of contents
	 *
	 * @param array $columns corresponds to columns to be rendered.
	 * @param array $matches
	 * @validate $columns TYPO3\CMS\Vidi\Domain\Validator\ColumnsValidator
	 * @validate $matches TYPO3\CMS\Vidi\Domain\Validator\MatchesValidator
	 * @return void
	 */
	public function listRowAction(array $columns = array(), $matches = array()) {

		// Initialize some objects related to the query
		$matcherObject = $this->createMatcherObject();
		foreach ($matches as $propertyName => $value) {
			$matcherObject->equals($propertyName, $value);
		}

		// Trigger signal for post processing Matcher Object.
		$this->emitPostProcessMatcherObjectSignal($matcherObject);

		$orderObject = $this->createOrderObject();
		$pagerObject = $this->createPagerObject();

		// Fetch the adequate repository
		$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance();

		// Query the repository
		$contents = $contentRepository->findBy($matcherObject, $orderObject, $pagerObject->getLimit(), $pagerObject->getOffset());
		$numberOfContents = $contentRepository->countBy($matcherObject);
		$pagerObject->setCount($numberOfContents);

		// Assign values
		$this->view->assign('columns', $columns);
		$this->view->assign('contents', $contents);
		$this->view->assign('numberOfContents', $numberOfContents);
		$this->view->assign('pager', $pagerObject);

		$this->request->setFormat('json');
		# Json header is not automatically respected in the BE with parameter format=json
		# so send one the hard way.
		header('Content-type: application/json');
	}

	/**
	 * List facet action for this controller. Output a json list of value
	 * corresponding of a searched facet.
	 * This action is expected to have a parameter format = json
	 *
	 * @param string $facet
	 * @param string $searchTerm
	 * @validate $facet TYPO3\CMS\Vidi\Domain\Validator\FacetValidator
	 * @return void
	 */
	public function listFacetValuesAction($facet, $searchTerm) {

		$tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService();
		$values = array();

//		@todo count contents and avoid too many items < 1000
//		$numberOfContents = $contentRepository->countBy($matcherObject);
		if ($tcaFieldService->hasRelation($facet)) {

			// Fetch the adequate repository
			$foreignTable = $tcaFieldService->getForeignTable($facet);
			$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance($foreignTable);
			$tcaTableService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getTableService($foreignTable);
			$contents = $contentRepository->findAll();

			foreach ($contents as $content) {
				$values[] = array (
					'value' => $content['uid'],
					'label' => $content[$tcaTableService->getLabelField()],
				);
			}
		} elseif (!$tcaFieldService->isTextArea($facet)) {

			// Fetch the adequate repository
			/** @var \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository $contentRepository */
			$contentRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance();

			// Query the repository
			$contents = $contentRepository->findDistinctValues($facet);
			foreach ($contents as $content) {
				$values[] = $content[$facet];
			}
		}

		# Json header is not automatically respected in the BE with parameter format=json
		# so send one the hard way.
		header('Content-type: application/json');
		return json_encode($values);
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

		/** @var ModuleLoader $moduleLoader */
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
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\Matcher
	 */
	protected function createMatcherObject() {

		/** @var $matcher \TYPO3\CMS\Vidi\Persistence\Matcher */
		$matcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Matcher');

		// Special case for Grid in the BE using jQuery DataTables plugin.
		// Retrieve a possible search term from GP.
		$searchTerm = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('sSearch');

		if (strlen($searchTerm) > 0) {

			$tcaFieldService = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getFieldService();

			// try to parse a json query
			$terms = json_decode($searchTerm, TRUE);
			if (is_array($terms)) {

				foreach ($terms as $term) {
					$fieldName = key($term);
					$value = current($term);
					if ($fieldName === 'text') {
						$matcher->setSearchTerm($value);
					} elseif (($tcaFieldService->hasRelation($fieldName) && is_numeric($value))
						|| $tcaFieldService->isNumerical($fieldName)) {
							$matcher->equals($fieldName, $value);
					} else {
						$matcher->likes($fieldName, $value);
					}
				}
			} else {
				$matcher->setSearchTerm($searchTerm);
			}
		}

		return $matcher;
	}

	/**
	 * Returns an order object.
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\Order
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
		return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Order', $order);
	}

	/**
	 * Returns a pager object.
	 *
	 * @return \TYPO3\CMS\Vidi\Persistence\Pager
	 */
	protected function createPagerObject() {

		/** @var $pager \TYPO3\CMS\Vidi\Persistence\Pager */
		$pager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Pager');

		// Set items per page
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('iDisplayLength') !== NULL) {
			$limit = (int) \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('iDisplayLength');
			$pager->setLimit($limit);
		}

		// Set offset
		$offset = 0;
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('iDisplayStart') !== NULL) {
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

	/**
	 * Signal that is called for post-processing a matcher object.
	 *
	 * @param \TYPO3\CMS\Vidi\Persistence\Matcher $matcherObject
	 * @signal
	 */
	protected function emitPostProcessMatcherObjectSignal(\TYPO3\CMS\Vidi\Persistence\Matcher $matcherObject) {

		/** @var ModuleLoader $moduleLoader */
		$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\ModuleLoader');

		$this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\Controller\Backend\ContentController', 'postProcessMatcherObject', array($matcherObject, $moduleLoader->getDataType()));
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
?>
