<?php
namespace TYPO3\CMS\Vidi\Controller\Backend;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Vidi\Behavior\SavingBehavior;
use TYPO3\CMS\Vidi\Domain\Repository\ContentRepositoryFactory;
use TYPO3\CMS\Vidi\Domain\Model\Content;
use TYPO3\CMS\Vidi\Persistence\Matcher;
use TYPO3\CMS\Vidi\Persistence\MatcherObjectFactory;
use TYPO3\CMS\Vidi\Persistence\OrderObjectFactory;
use TYPO3\CMS\Vidi\Persistence\PagerObjectFactory;
use TYPO3\CMS\Vidi\Signal\AfterFindContentObjectsSignalArguments;
use TYPO3\CMS\Vidi\Signal\ProcessContentDataSignalArguments;
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
		$order = OrderObjectFactory::getInstance()->getOrder();
		$pager = PagerObjectFactory::getInstance()->getPager();

		// Query the repository.
		$objects = ContentRepositoryFactory::getInstance()->findBy($matcher, $order, $pager->getLimit(), $pager->getOffset());
		$signalResult = $this->emitAfterFindContentObjectsSignal($objects, $matcher, $pager->getLimit(), $pager->getOffset());

		// Reset objects variable after possible signal / slot processing.
		$objects = $signalResult->getContentObjects();

		// Count number of content objects.
		if ($signalResult->getHasBeenProcessed()) {
			$numberOfContents = $signalResult->getNumberOfObjects();
		} else {
			$numberOfContents = ContentRepositoryFactory::getInstance()->countBy($matcher);
		}

		$pager->setCount($numberOfContents);

		// Assign values.
		$this->view->assign('columns', $columns);
		$this->view->assign('objects', $objects);
		$this->view->assign('numberOfContents', $numberOfContents);
		$this->view->assign('pager', $pager);
		$this->view->assign('response', $this->response);
	}

	/**
	 * Update content objects given matching criteria and returns a json result.
	 * Important to notice the field name can contains a path, e.g. metadata.title and therefore must be analysed.
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
	 * @param string $fieldNameAndPath
	 * @param array $content
	 * @param array $matches
	 * @param string $savingBehavior
	 * @return string
	 */
	public function updateAction($fieldNameAndPath, array $content, array $matches = array(), $savingBehavior = SavingBehavior::REPLACE) {

		$result = array(); // Initialize array.

		// Instantiate the Matcher object according different rules.
		$matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);
		$order = OrderObjectFactory::getInstance()->getOrder();

		// Query the repository given a matcher object.
		$objects = ContentRepositoryFactory::getInstance()->findBy($matcher, $order);
		$signalResult = $this->emitAfterFindContentObjectsSignal($objects, $matcher);

		// Reset objects variable after possible signal / slot processing.
		$objects = $signalResult->getContentObjects();

		$updatedFieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

		$numberOfObjects = count($objects);
		foreach ($objects as $counter => $object) {

			$identifier = $this->getContentObjectResolver()->getValue($object, $fieldNameAndPath, 'uid');
			$dataType = $this->getContentObjectResolver()->getDataType($object, $fieldNameAndPath);

			$signalResult = $this->emitProcessContentDataSignal($object, $fieldNameAndPath, $content, $counter + 1, $savingBehavior);

			$contentData = $signalResult->getContentData();

			// Add identifier to content data, required by TCEMain.
			$contentData['uid'] = $identifier;

			/** @var Content $dataObject */
			$dataObject = $this->objectManager->get('TYPO3\CMS\Vidi\Domain\Model\Content', $dataType, $contentData);

			$_result = array();
			$_result['status'] = ContentRepositoryFactory::getInstance($dataType)->update($dataObject);
			$_result['message'] = ContentRepositoryFactory::getInstance($dataType)->getErrorMessage();
			$_result['updatedField'] = $updatedFieldName;
			$_result['action'] = 'update';
			$_result['identifier'] = $object->getUid();
			$_result['dataType'] = $object->getDataType();

			// We only want to see the detail result if there is one object updated.
			// It would cost time for nothing in case of mass update.
			if ($_result['status'] && $numberOfObjects === 1) {

				// Reload the updated object from repository.
				$updatedObject = ContentRepositoryFactory::getInstance()->findByUid($object->getUid());

				// Re-fetch the updated result.
				$updatedResult = $this->getContentObjectResolver()->getValue($updatedObject, $fieldNameAndPath, $updatedFieldName);
				if (is_array($updatedResult)) {
					$_updatedResult = array(); // reset result set.

					/** @var Content $contentObject */
					foreach ($updatedResult as $contentObject) {
						$labelField = TcaService::table($contentObject)->getLabelField();
						$values = array(
							'uid' => $contentObject->getUid(),
							$labelField => $contentObject[$labelField],
						);
						$_updatedResult[] = $values;
					}

					$updatedResult = $_updatedResult;
				}

				$_result['object'] = array(
					'uid' => $this->getContentObjectResolver()->getValue($object, $fieldNameAndPath, 'uid'),
					$updatedFieldName => $updatedResult,
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
	 * Argument $field corresponds to the field name to be edited.
	 *
	 * @param string $fieldNameAndPath
	 * @param array $matches
	 * @throws \Exception
	 */
	public function editAction($fieldNameAndPath, array $matches = array()) {

		// Instantiate the Matcher object according different rules.
		$matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);

		// Query the repository given a matcher object.
		$objects = ContentRepositoryFactory::getInstance()->findBy($matcher);
		$signalResult = $this->emitAfterFindContentObjectsSignal($objects, $matcher);

		if ($signalResult->getHasBeenProcessed()) {
			$numberOfObjects = $signalResult->getNumberOfObjects();
		} else {
			// @todo find out whether it is faster a double query as find + count in PHP.
			$numberOfObjects = ContentRepositoryFactory::getInstance()->countBy($matcher);
		}

		$dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
		$fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

		$fieldType = TcaService::table($dataType)->field($fieldName)->getType();
		$this->view->assign('fieldType', ucfirst($fieldType));
		$this->view->assign('dataType', $dataType);
		$this->view->assign('fieldName', $fieldName);
		$this->view->assign('matches', $matches);
		$this->view->assign('fieldNameAndPath', $fieldNameAndPath);
		$this->view->assign('numberOfObjects', $numberOfObjects);
		$this->view->assign('editWholeSelection', empty($matches['uid'])); // necessary??

		// Fetch content and its relations.
		if ($fieldType === TcaService::MULTISELECT) {

			$object = ContentRepositoryFactory::getInstance()->findOneBy($matcher);
			$identifier = $this->getContentObjectResolver()->getValue($object, $fieldNameAndPath, 'uid');
			$dataType = $this->getContentObjectResolver()->getDataType($object, $fieldNameAndPath);

			$content = ContentRepositoryFactory::getInstance($dataType)->findByUid($identifier);

			if (!$content) {
				$message = sprintf('I could not retrieved content object of type "%s" with identifier %s.', $dataType, $identifier);
				throw new \Exception($message, 1402350182);
			}

			$relatedDataType = TcaService::table($dataType)->field($fieldName)->getForeignTable();

			// Initialize the matcher object.
			$matcher = MatcherObjectFactory::getInstance()->getMatcher(array(), $relatedDataType);

			// Default ordering for related data type.
			$defaultOrderings = TcaService::table($relatedDataType)->getDefaultOrderings();
			$order = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Persistence\Order', $defaultOrderings);

			$relatedContents = ContentRepositoryFactory::getInstance($relatedDataType)->findBy($matcher, $order);

			$this->view->assign('content', $content);
			$this->view->assign('relatedContents', $relatedContents);
			$this->view->assign('relatedDataType', $relatedDataType);
			$this->view->assign('relatedContentTitle', TcaService::table($relatedDataType)->getTitle());
		}
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

		// Query the repository.
		$objects = ContentRepositoryFactory::getInstance()->findBy($matcher);
		$signalResult = $this->emitAfterFindContentObjectsSignal($objects, $matcher);

		// Reset objects variable after possible signal / slot processing.
		$objects = $signalResult->getContentObjects();

		// Compute the label field name of the table.
		$tableTitleField = TcaService::table()->getLabelField();

		$result = array();
		foreach ($objects as $object) {

			$tableTitleValue = $object[$tableTitleField];

			$_result = array();
			$_result['status'] = ContentRepositoryFactory::getInstance()->remove($object);
			$_result['message'] = ContentRepositoryFactory::getInstance()->getErrorMessage();
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
	 * @return \TYPO3\CMS\Vidi\Module\ModuleLoader
	 */
	protected function getModuleLoader() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Module\ModuleLoader');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\ContentObjectResolver
	 */
	protected function getContentObjectResolver() {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\ContentObjectResolver');
	}

	/**
	 * @return \TYPO3\CMS\Vidi\Resolver\FieldPathResolver
	 */
	protected function getFieldPathResolver () {
		return GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Resolver\FieldPathResolver');
	}

	/**
	 * Signal that is called after the content objects have been found.
	 *
	 * @param array $contentObjects
	 * @param \TYPO3\CMS\Vidi\Persistence\Matcher $matcher
	 * @param int $limit
	 * @param int $offset
	 * @return AfterFindContentObjectsSignalArguments
	 * @signal
	 */
	protected function emitAfterFindContentObjectsSignal($contentObjects, Matcher $matcher, $limit = 0, $offset = 0) {

		/** @var \TYPO3\CMS\Vidi\Signal\AfterFindContentObjectsSignalArguments $signalArguments */
		$signalArguments = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Signal\AfterFindContentObjectsSignalArguments');
		$signalArguments->setDataType($this->getModuleLoader()->getDataType())
			->setContentObjects($contentObjects)
			->setMatcher($matcher)
			->setLimit($limit)
			->setOffset($offset)
			->setHasBeenProcessed(FALSE);

		$signalResult = $this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\Controller\Backend\ContentController', 'afterFindContentObjects', array($signalArguments));
		return $signalResult[0];
	}

	/**
	 * Signal that is called for post-processing content data send to the server for update.
	 *
	 * @param Content $contentObject
	 * @param $fieldNameAndPath
	 * @param $contentData
	 * @param $counter
	 * @param $savingBehavior
	 * @return ProcessContentDataSignalArguments
	 * @signal
	 */
	protected function emitProcessContentDataSignal(Content $contentObject, $fieldNameAndPath, $contentData, $counter, $savingBehavior) {

		/** @var \TYPO3\CMS\Vidi\Signal\ProcessContentDataSignalArguments $signalArguments */
		$signalArguments = GeneralUtility::makeInstance('TYPO3\CMS\Vidi\Signal\ProcessContentDataSignalArguments');
		$signalArguments->setContentObject($contentObject)
			->setFieldNameAndPath($fieldNameAndPath)
			->setContentData($contentData)
			->setCounter($counter)
			->setSavingBehavior($savingBehavior);

		$signalResult = $this->getSignalSlotDispatcher()->dispatch('TYPO3\CMS\Vidi\Controller\Backend\ContentController', 'processContentData', array($signalArguments));
		return $signalResult[0];
	}

	/**
	 * Get the SignalSlot dispatcher.
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		return $this->objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	}
}
