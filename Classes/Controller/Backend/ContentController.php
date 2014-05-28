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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Vidi\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Persistence\MatcherObjectFactory;
use TYPO3\CMS\Vidi\Persistence\OrderObjectFactory;
use TYPO3\CMS\Vidi\Persistence\PagerObjectFactory;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Controller which handles actions related to Vidi in the Backend.
 */
class ContentController extends ActionController {

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
	public function indexAction() {
		$this->view->assign('columns', TcaService::grid()->getFields());
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
	public function listAction(array $columns = array(), $matches = array()) {

		// Initialize some objects related to the query.
		$matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);
		$orderObject = OrderObjectFactory::getInstance()->getOrder();
		$pagerObject = PagerObjectFactory::getInstance()->getPager();

		// Fetch the adequate repository.
		$contentRepository = ContentRepositoryFactory::getInstance();

		// Query the repository.
		$objects = $contentRepository->findBy($matcher, $orderObject, $pagerObject->getLimit(), $pagerObject->getOffset());
		$numberOfContents = $contentRepository->countBy($matcher);
		$pagerObject->setCount($numberOfContents);

		// Assign values.
		$this->view->assign('columns', $columns);
		$this->view->assign('objects', $objects);
		$this->view->assign('numberOfContents', $numberOfContents);
		$this->view->assign('pager', $pagerObject);
		$this->view->assign('response', $this->response);
	}

	/**
	 * @param array $content
	 * @param string $dataType
	 * @return string
	 */
	public function updateAction(array $content = array(), $dataType = '') {

		$dataType = empty($dataType) ? $this->getModuleLoader()->getDataType() : $dataType;

		/** @var \TYPO3\CMS\Vidi\Domain\Validator\ContentValidator $contentValidator */
		$contentValidator = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Validator\ContentValidator');
		$contentValidator->validate($content, $dataType);

		// Fetch the adequate repository.
		$contentRepository = ContentRepositoryFactory::getInstance($dataType);

		// Instantiate Content object.
		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $object */
		$object = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Model\Content', $dataType, $content);
		$contentRepository->update($object);

		// Reload the updated object from repository.
		$object = $contentRepository->findByUid($object->getUid());

		// Extract keys of content.
		$keys = array_keys($content);

		// Assuming the field name is the first parameter of content.
		$fieldName = array_shift($keys);
		return $object[$fieldName];
	}

	/**
	 * Delete a row given an object uid.
	 * This action is expected to have a parameter format = json
	 *
	 * @param array $matches
	 * @return string
	 */
	public function deleteAction(array $matches = array()) {

		$matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);

		// Fetch the adequate repository
		$contentRepository = ContentRepositoryFactory::getInstance();

		// Query the repository.
		$objects = $contentRepository->findBy($matcher);

		// Compute the label field name of the table.
		$tableTitleField = TcaService::table()->getLabelField();

		$result = array();
		foreach ($objects as $object) {

			$tableTitleValue = $object[$tableTitleField];

			$_result = array();
			$_result['status'] = $contentRepository->remove($object);
			$_result['message'] = $contentRepository->getErrorMessage();
			$_result['action'] = 'delete';
			if ($_result['status']) {
				$_result['object'] = array(
					'uid' => $object->getUid(),
					$tableTitleField => $tableTitleValue,
				);
			}
			$result[] = $_result;
		}

		# Json header is not automatically sent in the BE...
		$this->response->setHeader('Content-Type', 'application/json');
		$this->response->sendHeaders();
		return json_encode($result);
	}

	/**
	 * Get the Vidi Module Loader.
	 *
	 * @return \TYPO3\CMS\Vidi\ModuleLoader
	 */
	protected function getModuleLoader() {
		return $this->objectManager->get('TYPO3\CMS\Vidi\ModuleLoader');
	}
}
