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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Vidi\Converter\ContentConverter;
use TYPO3\CMS\Vidi\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Converter\FieldConverter;
use TYPO3\CMS\Vidi\Domain\Model\Content;
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
	 * Update content objects given matching criteria and returns a json result.
	 * Only set argument $dataType if the data type to be edited does not correspond of the expected data type of the module.
	 * This case can be seen in File: sys_file <-> sys_file_metadata <-> sys_category.
	 * The data type will be for many cases "sys_file_metadata" for the main "sys_file" module.
	 *
	 * Possible values for $matches:
	 * -----------------------------
	 *
	 * $matches = array(uid => 1), will be taken as $query->equals
	 * $matches = array(uid => 1,2,3), will be taken as $query->in
	 * $matches = array(field_name1 => bar, field_name2 => bax), will be separated by AND.
	 *
	 * Possible values for $content:
	 * -----------------------------
	 *
	 * $content = array(field_name => bar)
	 * $content = array(field_name => array(value1, value2)) <-- will be CSV converted by "value1,value2"
	 *
	 * @param array $content
	 * @param array $matches
	 * @param string $dataType
	 * @return string
	 */
	public function updateAction(array $content, array $matches, $dataType = NULL) {

		$dataType = is_null($dataType) ? $this->getModuleLoader()->getDataType() : $dataType;

		// Instantiate the Matcher object according different rules.
		$matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $dataType);

		// Fetch the adequate repository.
		$contentRepository = ContentRepositoryFactory::getInstance($dataType);

		// Query the repository given a matcher object.
		$objects = $contentRepository->findBy($matcher);

		// Assume (naively) the first field in content is the "main" field
		// to be edited and will be returned in the JSON response.
		$updatedField = key($content);

		$result = array();
		foreach ($objects as $object) {

			// Add identifier to content data.
			$content['uid'] = $object->getUid();

			/** @var Content $dataObject */
			$dataObject = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Model\Content', $object->getDataType(), $content);

			$_result = array();
			$_result['status'] = $contentRepository->update($dataObject);
			$_result['message'] = $contentRepository->getErrorMessage();
			$_result['updatedField'] = $updatedField;
			$_result['action'] = 'update';
			if ($_result['status']) {

				// Reload the updated object from repository.
				$updatedObject = $contentRepository->findByUid($object->getUid());

				// Fetch the updated result.
				$updatedResult = $updatedObject[$updatedField];
				if (is_array($updatedResult)) {
					$updatedResult = array(); // reset result set.
					/** @var Content $contentObject */
					foreach ($updatedObject[$updatedField] as $contentObject) {
						$labelField = TcaService::table($contentObject)->getLabelField();
						$values = array(
							'uid' => $contentObject->getUid(),
							$labelField => $contentObject[$labelField],
						);
						$updatedResult[] = $values;
					}
				}

				$_result['object'] = array(
					'uid' => $object->getUid(),
					$updatedField => $updatedResult,
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
	 * Returns an editing form for a given field name of a Content object.
	 * Argument $fieldName corresponds to the field name to be edited.
	 *
	 * @param int $contentIdentifier
	 * @param string $fieldName
	 * @param string $dataType
	 * @throws \Exception
	 */
	public function editAction($contentIdentifier, $fieldName, $dataType = NULL) {

		$dataType = is_null($dataType) ? $this->getModuleLoader()->getDataType() : $dataType;
		$contentRepository = ContentRepositoryFactory::getInstance($dataType);
		$content = $contentRepository->findByUid($contentIdentifier);

		if (!$content) {
			$message = sprintf('I could not retrieved content object of type "%s" with identifier %s.', $dataType, $contentIdentifier);
			throw new \Exception($message, 1402350182);
		}

		$relatedDataType = TcaService::table($dataType)->field($fieldName)->getForeignTable();
		$relatedContentRepository = ContentRepositoryFactory::getInstance($relatedDataType);

		// Initialize the matcher object.
		$matcher = MatcherObjectFactory::getInstance()->getMatcher(array(), $relatedDataType);
		$order = OrderObjectFactory::getInstance()->getOrder($relatedDataType);

		$relatedContents = $relatedContentRepository->findBy($matcher, $order);

		$this->view->assign('dataType', $dataType);
		$this->view->assign('content', $content);
		$this->view->assign('fieldName', $fieldName);
		$this->view->assign('relatedContents', $relatedContents);
		$this->view->assign('relatedDataType', $relatedDataType);
		$this->view->assign('relatedContentTitle', TcaService::table($relatedDataType)->getTitle());
	}

	/**
	 * Delete rows given matching criteria and returns a json result.
	 *
	 * Possible values for $matches:
	 * -----------------------------
	 *
	 * $matches = array(uid => 1), will be taken as $query->equals
	 * $matches = array(uid => 1,2,3), will be taken as $query->in
	 * $matches = array(field_name1 => bar, field_name2 => bax), will be separated by AND.
	 *
	 * @param array $matches
	 * @return string
	 */
	public function deleteAction(array $matches) {

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
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\ModuleLoader');
	}
}
