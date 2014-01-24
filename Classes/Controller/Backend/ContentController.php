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
use TYPO3\CMS\Vidi\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\ModuleLoader;
use TYPO3\CMS\Vidi\PersistenceObjectFactory;
use TYPO3\CMS\Vidi\Tca\TcaService;

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

		$viewHelpers = $moduleLoader->getNavigationTopLeftComponents();
		$this->view->assign('navigationTopLeftComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getNavigationTopRightComponents();
		$this->view->assign('navigationTopRightComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getNavigationBottomLeftComponents();
		$this->view->assign('navigationBottomLeftComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getNavigationBottomRightComponents();
		$this->view->assign('navigationBottomRightComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getGridTopComponents();
		$this->view->assign('gridTopComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getGridBottomComponents();
		$this->view->assign('gridBottomComponents', $this->viewHelperRenderer->render($viewHelpers));

		$viewHelpers = $moduleLoader->getGridMenuComponents();
		$this->view->assign('gridMenuComponents', $this->viewHelperRenderer->render($viewHelpers));

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
	public function listRowAction(array $columns = array(), $matches = array()) {

		// Initialize some objects related to the query
		$matcherObject = PersistenceObjectFactory::getInstance()->getMatcherObject();
		foreach ($matches as $propertyName => $value) {
			$matcherObject->equals($propertyName, $value);
		}

		$orderObject = PersistenceObjectFactory::getInstance()->getOrderObject();
		$pagerObject = PersistenceObjectFactory::getInstance()->getPagerObject();

		// Fetch the adequate repository
		$contentRepository = ContentRepositoryFactory::getInstance();

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
	 * @todo possibly implement a type converter for argument $content.
	 * @param array $content
	 * @param string $dataType
	 * @return string
	 */
	public function updateAction(array $content = array(), $dataType = '') {

		/** @var ModuleLoader $moduleLoader */
		$moduleLoader = $this->objectManager->get('TYPO3\CMS\Vidi\ModuleLoader');
		$dataType = empty($dataType) ? $moduleLoader->getDataType() : $dataType;

		/** @var \TYPO3\CMS\Vidi\Domain\Validator\ContentValidator $contentValidator */
		$contentValidator = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Validator\ContentValidator');
		$contentValidator->validate($content, $dataType);

		// Fetch the adequate repository
		$contentRepository = ContentRepositoryFactory::getInstance($dataType);

		// Instantiate Content object.
		/** @var \TYPO3\CMS\Vidi\Domain\Model\Content $contentObject */
		$contentObject = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Model\Content', $dataType, $content);
		$contentRepository->update($contentObject);

		// reload the updated object from repository
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
		$contentRepository = ContentRepositoryFactory::getInstance();

		$labelField = TcaService::table()->getLabelField();
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
}
?>
