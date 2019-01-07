<?php
namespace Fab\Vidi\Controller;

/*
 * This file is part of the Fab/Vidi project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Language\LanguageService;
use Fab\Vidi\Module\ModuleLoader;
use Fab\Vidi\Persistence\Matcher;
use Fab\Vidi\Persistence\Order;
use Fab\Vidi\Resolver\ContentObjectResolver;
use Fab\Vidi\Resolver\FieldPathResolver;
use Fab\Vidi\Service\ClipboardService;
use Fab\Vidi\Service\ContentService;
use Fab\Vidi\Tca\FieldType;
use Fab\Vidi\TypeConverter\CsvToArrayConverter;
use Fab\Vidi\View\Grid\Row;
use Fab\Vidi\View\Uri\EditUri;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Fab\Vidi\Behavior\SavingBehavior;
use Fab\Vidi\Domain\Repository\ContentRepositoryFactory;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Mvc\JsonView;
use Fab\Vidi\Mvc\JsonResult;
use Fab\Vidi\Persistence\MatcherObjectFactory;
use Fab\Vidi\Persistence\OrderObjectFactory;
use Fab\Vidi\Persistence\PagerObjectFactory;
use Fab\Vidi\Signal\ProcessContentDataSignalArguments;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Controller which handles actions related to Vidi in the Backend.
 */
class ContentController extends ActionController
{

    /**
     * @var \Fab\Vidi\Domain\Repository\SelectionRepository
     * @inject
     */
    protected $selectionRepository;

    /**
     * Initialize every action.
     */
    public function initializeAction()
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addInlineLanguageLabelFile('EXT:vidi/Resources/Private/Language/locallang.xlf');

        // Configure property mapping to retrieve the file object.
        if ($this->arguments->hasArgument('columns')) {

            /** @var CsvToArrayConverter $typeConverter */
            $typeConverter = $this->objectManager->get(CsvToArrayConverter::class);

            $propertyMappingConfiguration = $this->arguments->getArgument('columns')->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->setTypeConverter($typeConverter);
        }
    }

    /**
     * List action for this controller.
     *
     * @return void
     */
    public function indexAction()
    {
        $dataType = $this->getModuleLoader()->getDataType();
        $selections = $this->selectionRepository->findByDataTypeForCurrentBackendUser($dataType);
        $this->view->assign('selections', $selections);

        $columns = Tca::grid()->getFields();
        $this->view->assign('columns', $columns);
        $this->view->assign('numberOfColumns', count($columns));
    }

    /**
     * List Row action for this controller. Output a json list of contents
     *
     * @param array $columns corresponds to columns to be rendered.
     * @param array $matches
     * @validate $columns Fab\Vidi\Domain\Validator\ColumnsValidator
     * @validate $matches Fab\Vidi\Domain\Validator\MatchesValidator
     * @return void
     */
    public function listAction(array $columns = [], $matches = [])
    {
        // Initialize some objects related to the query.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);
        $order = OrderObjectFactory::getInstance()->getOrder();
        $pager = PagerObjectFactory::getInstance()->getPager();

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher, $order, $pager->getLimit(), $pager->getOffset());
        $pager->setCount($contentService->getNumberOfObjects());

        // Assign values.
        $this->view->assign('columns', $columns);
        $this->view->assign('objects', $contentService->getObjects());
        $this->view->assign('numberOfObjects', $contentService->getNumberOfObjects());
        $this->view->assign('pager', $pager);
        $this->view->assign('response', $this->response);
    }

    /**
     * Retrieve Content objects first according to matching criteria and then "update" them.
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
     * @param int $language
     * @param array $columns
     * @return string
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     */
    public function updateAction($fieldNameAndPath, array $content, array $matches = [], $savingBehavior = SavingBehavior::REPLACE, $language = 0, $columns = [])
    {

        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);
        $order = OrderObjectFactory::getInstance()->getOrder();

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher, $order);

        // Get the real field that is going to be updated.
        $updatedFieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

        // Get result object for storing data along the processing.
        $result = $this->getJsonResult();
        $result->setNumberOfObjects($contentService->getNumberOfObjects());

        foreach ($contentService->getObjects() as $index => $object) {

            $identifier = $this->getContentObjectResolver()->getValue($object, $fieldNameAndPath, 'uid', $language);

            // It could be the identifier is not found because the translation
            // of the record does not yet exist when mass-editing
            if ((int)$identifier <= 0) {
                continue;
            }

            $dataType = $this->getContentObjectResolver()->getDataType($object, $fieldNameAndPath);

            $signalResult = $this->emitProcessContentDataSignal($object, $fieldNameAndPath, $content, $index + 1, $savingBehavior, $language);
            $contentData = $signalResult->getContentData();

            // Add identifier to content data, required by TCEMain.
            $contentData['uid'] = $identifier;

            /** @var Content $dataObject */
            $dataObject = GeneralUtility::makeInstance(Content::class, $dataType, $contentData);

            // Properly update object.
            ContentRepositoryFactory::getInstance($dataType)->update($dataObject);

            // Get the possible error messages and store them.
            $errorMessages = ContentRepositoryFactory::getInstance()->getErrorMessages();
            $result->addErrorMessages($errorMessages);

            // We only want to see the detail result if there is one object updated.
            // Required for inline editing + it will display some useful info on the GUI in the flash messages.
            if ($contentService->getNumberOfObjects() === 1) {

                // Fetch the updated object from repository.
                $updatedObject = ContentRepositoryFactory::getInstance()->findByUid($object->getUid());

                // Re-fetch the updated result.
                $updatedResult = $this->getContentObjectResolver()->getValue($updatedObject, $fieldNameAndPath, $updatedFieldName, $language);
                if (is_array($updatedResult)) {
                    $_updatedResult = []; // reset result set.

                    /** @var Content $contentObject */
                    foreach ($updatedResult as $contentObject) {
                        $labelField = Tca::table($contentObject)->getLabelField();
                        $values = array(
                            'uid' => $contentObject->getUid(),
                            'name' => $contentObject[$labelField],
                        );
                        $_updatedResult[] = $values;
                    }

                    $updatedResult = $_updatedResult;
                }

                $labelField = Tca::table($object)->getLabelField();

                $processedObjectData = array(
                    'uid' => $object->getUid(),
                    'name' => $object[$labelField],
                    'updatedField' => $fieldNameAndPath,
                    'updatedValue' => $updatedResult,
                );
                $result->setProcessedObject($processedObjectData);

                if (!empty($columns)) {
                    /** @var Row $row */
                    $row = GeneralUtility::makeInstance(\Fab\Vidi\View\Grid\Row::class, $columns);
                    $result->setRow($row->render($updatedObject));
                }
            }
        }

        // Set the result and render the JSON view.
        $this->getJsonView()->setResult($result);
        return $this->getJsonView()->render();
    }

    /**
     * Set the sorting of a record giving the previous object.
     *
     * @param array $matches
     * @param int $previousIdentifier
     * @return string
     */
    public function sortAction(array $matches = [], $previousIdentifier = null)
    {

        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        // Compute the label field name of the table.
        $tableTitleField = Tca::table()->getLabelField();

        // Get result object for storing data along the processing.
        $result = $this->getJsonResult();
        $result->setNumberOfObjects($contentService->getNumberOfObjects());

        foreach ($contentService->getObjects() as $object) {

            // Store the first object, so that the "action" message can be more explicit when deleting only one record.
            if ($contentService->getNumberOfObjects() === 1) {
                $tableTitleValue = $object[$tableTitleField];
                $processedObjectData = array(
                    'uid' => $object->getUid(),
                    'name' => $tableTitleValue,
                );
                $result->setProcessedObject($processedObjectData);
            }

            // The $target corresponds to the pid to move the records to.
            // It can also be a negative value in case of sorting. The negative value would be the uid of its predecessor.
            $target = is_null($previousIdentifier) ? $object->getPid() : (-(int)$previousIdentifier);

            // Work out the object.
            ContentRepositoryFactory::getInstance()->move($object, $target);

            // Get the possible error messages and store them.
            $errorMessages = ContentRepositoryFactory::getInstance()->getErrorMessages();
            $result->addErrorMessages($errorMessages);
        }

        // Set the result and render the JSON view.
        $this->getJsonView()->setResult($result);
        return $this->getJsonView()->render();
    }

    /**
     * Returns an editing form for a given field name of a Content object.
     * Argument $fieldNameAndPath corresponds to the field name to be edited.
     * Important to notice it can contains a path, e.g. metadata.title and therefore must be analysed.
     *
     * Possible values for $matches, refer to method "updateAction".
     *
     * @param string $fieldNameAndPath
     * @param array $matches
     * @param bool $hasRecursiveSelection
     * @throws \Exception
     */
    public function editAction($fieldNameAndPath, array $matches = [], $hasRecursiveSelection = false)
    {

        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        $dataType = $this->getFieldPathResolver()->getDataType($fieldNameAndPath);
        $fieldName = $this->getFieldPathResolver()->stripFieldPath($fieldNameAndPath);

        $fieldType = Tca::table($dataType)->field($fieldName)->getType();
        $this->view->assign('fieldType', ucfirst($fieldType));
        $this->view->assign('dataType', $dataType);
        $this->view->assign('fieldName', $fieldName);
        $this->view->assign('matches', $matches);
        $this->view->assign('fieldNameAndPath', $fieldNameAndPath);
        $this->view->assign('numberOfObjects', $contentService->getNumberOfObjects());
        $this->view->assign('hasRecursiveSelection', $hasRecursiveSelection);
        $this->view->assign('editWholeSelection', empty($matches['uid'])); // necessary??

        // Fetch content and its relations.
        if ($fieldType === FieldType::MULTISELECT) {

            $object = ContentRepositoryFactory::getInstance()->findOneBy($matcher);
            $identifier = $this->getContentObjectResolver()->getValue($object, $fieldNameAndPath, 'uid');
            $dataType = $this->getContentObjectResolver()->getDataType($object, $fieldNameAndPath);

            $content = ContentRepositoryFactory::getInstance($dataType)->findByUid($identifier);

            // Makes sure the object was retrieved. Security!
            if (!$content) {
                $message = sprintf('I could not retrieved content object of type "%s" with identifier %s.', $dataType, $identifier);
                throw new \Exception($message, 1402350182);
            }

            $relatedDataType = Tca::table($dataType)->field($fieldName)->getForeignTable();

            // Initialize the matcher object.
            /** @var Matcher $matcher */
            $matcher = GeneralUtility::makeInstance(Matcher::class, [], $relatedDataType);

            // Default ordering for related data type.
            $defaultOrderings = Tca::table($relatedDataType)->getDefaultOrderings();
            /** @var Order $order */
            $defaultOrder = GeneralUtility::makeInstance(Order::class, $defaultOrderings);

            // Fetch related contents
            $relatedContents = ContentRepositoryFactory::getInstance($relatedDataType)->findBy($matcher, $defaultOrder);

            if (Tca::table($dataType)->field($fieldName)->isRenderModeTree()) {

                $fieldConfiguration = Tca::table($dataType)->field($fieldName)->getConfiguration();
                $parentField = $fieldConfiguration['treeConfig']['parentField'];

                $flatTree = [];
                foreach ($relatedContents as $node) {
                    $flatTree[$node->getUid()] = array(
                        'item' => $node,
                        'parent' => $node[$parentField] ? $node[$parentField]['uid'] : null,
                    );
                }

                $tree = [];

                // If leaves are selected without its parents selected, those are shown as parent
                foreach ($flatTree as $id => &$flatNode) {
                    if (!isset($flatTree[$flatNode['parent']])) {
                        $flatNode['parent'] = null;
                    }
                }

                foreach ($flatTree as $id => &$node) {
                    if ($node['parent'] === null) {
                        $tree[$id] = &$node;
                    } else {
                        $flatTree[$node['parent']]['children'][$id] = &$node;
                    }
                }

                $relatedContents = $tree;
            }

            $this->view->assign('content', $content);
            $this->view->assign('relatedContents', $relatedContents);
            $this->view->assign('relatedDataType', $relatedDataType);
            $this->view->assign('relatedContentTitle', Tca::table($relatedDataType)->getTitle());
            $this->view->assign(
                'renderMode',
                Tca::table($dataType)->field($fieldName)->isRenderModeTree() ? FieldType::TREE : null
            );
        }
    }

    /**
     * Retrieve Content objects first according to matching criteria and then "delete" them.
     *
     * Possible values for $matches, refer to method "updateAction".
     *
     * @param array $matches
     * @return string
     */
    public function deleteAction(array $matches = [])
    {

        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        // Compute the label field name of the table.
        $tableTitleField = Tca::table()->getLabelField();

        // Get result object for storing data along the processing.
        $result = $this->getJsonResult();
        $result->setNumberOfObjects($contentService->getNumberOfObjects());

        foreach ($contentService->getObjects() as $object) {

            // Store the first object, so that the delete message can be more explicit when deleting only one record.
            if ($contentService->getNumberOfObjects() === 1) {
                $tableTitleValue = $object[$tableTitleField];
                $processedObjectData = array(
                    'uid' => $object->getUid(),
                    'name' => $tableTitleValue,
                );
                $result->setProcessedObject($processedObjectData);
            }

            // Properly delete object.
            ContentRepositoryFactory::getInstance()->remove($object);

            // Get the possible error messages and store them.
            $errorMessages = ContentRepositoryFactory::getInstance()->getErrorMessages();
            $result->addErrorMessages($errorMessages);
        }

        // Set the result and render the JSON view.
        $this->getJsonView()->setResult($result);
        return $this->getJsonView()->render();
    }

    /**
     * Retrieve Content objects first according to matching criteria and then "copy" them.
     *
     * Possible values for $matches, refer to method "updateAction".
     *
     * @param string $target
     * @param array $matches
     * @throws \Exception
     * @return string
     */
    public function copyAction($target, array $matches = [])
    {
        // @todo
        throw new \Exception('Not yet implemented', 1410192546);
    }

    /**
     * Retrieve Content objects from the Clipboard then "copy" them according to the target.
     *
     * @param string $target
     * @throws \Exception
     * @return string
     */
    public function copyClipboardAction($target)
    {

        // Retrieve matcher object from clipboard.
        $matcher = $this->getClipboardService()->getMatcher();

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        // Compute the label field name of the table.
        $tableTitleField = Tca::table()->getLabelField();

        // Get result object for storing data along the processing.
        $result = $this->getJsonResult();
        $result->setNumberOfObjects($contentService->getNumberOfObjects());

        foreach ($contentService->getObjects() as $object) {

            // Store the first object, so that the "action" message can be more explicit when deleting only one record.
            if ($contentService->getNumberOfObjects() === 1) {
                $tableTitleValue = $object[$tableTitleField];
                $processedObjectData = array(
                    'uid' => $object->getUid(),
                    'name' => $tableTitleValue,
                );
                $result->setProcessedObject($processedObjectData);
            }

            // Work out the object.
            ContentRepositoryFactory::getInstance()->copy($object, $target);

            // Get the possible error messages and store them.
            $errorMessages = ContentRepositoryFactory::getInstance()->getErrorMessages();
            $result->addErrorMessages($errorMessages);
        }

        // Flush Clipboard if told so.
        if (GeneralUtility::_GP('flushClipboard')) {
            $this->getClipboardService()->flush();
        }

        // Set the result and render the JSON view.
        $this->getJsonView()->setResult($result);
        return $this->getJsonView()->render();
    }

    /**
     * Retrieve Content objects first according to matching criteria and then "move" them.
     *
     * Possible values for $matches, refer to method "updateAction".
     *
     * @param string $target
     * @param array $matches
     * @return string
     */
    public function moveAction($target, array $matches = [])
    {

        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        // Compute the label field name of the table.
        $tableTitleField = Tca::table()->getLabelField();

        // Get result object for storing data along the processing.
        $result = $this->getJsonResult();
        $result->setNumberOfObjects($contentService->getNumberOfObjects());

        foreach ($contentService->getObjects() as $object) {

            // Store the first object, so that the "action" message can be more explicit when deleting only one record.
            if ($contentService->getNumberOfObjects() === 1) {
                $tableTitleValue = $object[$tableTitleField];
                $processedObjectData = array(
                    'uid' => $object->getUid(),
                    'name' => $tableTitleValue,
                );
                $result->setProcessedObject($processedObjectData);
            }

            // Work out the object.
            ContentRepositoryFactory::getInstance()->move($object, $target);

            // Get the possible error messages and store them.
            $errorMessages = ContentRepositoryFactory::getInstance()->getErrorMessages();
            $result->addErrorMessages($errorMessages);
        }

        // Set the result and render the JSON view.
        $this->getJsonView()->setResult($result);
        return $this->getJsonView()->render();
    }

    /**
     * Retrieve Content objects from the Clipboard then "move" them according to the target.
     *
     * @param string $target
     * @return string
     */
    public function moveClipboardAction($target)
    {

        // Retrieve matcher object from clipboard.
        $matcher = $this->getClipboardService()->getMatcher();

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        // Compute the label field name of the table.
        $tableTitleField = Tca::table()->getLabelField();

        // Get result object for storing data along the processing.
        $result = $this->getJsonResult();
        $result->setNumberOfObjects($contentService->getNumberOfObjects());

        foreach ($contentService->getObjects() as $object) {

            // Store the first object, so that the "action" message can be more explicit when deleting only one record.
            if ($contentService->getNumberOfObjects() === 1) {
                $tableTitleValue = $object[$tableTitleField];
                $processedObjectData = array(
                    'uid' => $object->getUid(),
                    'name' => $tableTitleValue,
                );
                $result->setProcessedObject($processedObjectData);
            }

            // Work out the object.
            ContentRepositoryFactory::getInstance()->move($object, $target);

            // Get the possible error messages and store them.
            $errorMessages = ContentRepositoryFactory::getInstance()->getErrorMessages();
            $result->addErrorMessages($errorMessages);
        }

        // Flush Clipboard if told so.
        if (GeneralUtility::_GP('flushClipboard')) {
            $this->getClipboardService()->flush();
        }

        // Set the result and render the JSON view.
        $this->getJsonView()->setResult($result);
        return $this->getJsonView()->render();
    }

    /**
     * Retrieve Content objects first according to matching criteria and then "localize" them.
     *
     * Possible values for $matches, refer to method "updateAction".
     *
     * @param string $fieldNameAndPath
     * @param array $matches
     * @param int $language
     * @return string
     * @throws \Exception
     */
    public function localizeAction($fieldNameAndPath, array $matches = [], $language = 0)
    {

        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        // Get result object for storing data along the processing.
        $result = $this->getJsonResult();
        $result->setNumberOfObjects($contentService->getNumberOfObjects());

        foreach ($contentService->getObjects() as $object) {

            $identifier = $this->getContentObjectResolver()->getValue($object, $fieldNameAndPath, 'uid');
            $dataType = $this->getContentObjectResolver()->getDataType($object, $fieldNameAndPath);

            // Fetch the source object to be localized.
            /** @var Content $content */
            $content = ContentRepositoryFactory::getInstance($dataType)->findByIdentifier($identifier);

            // Makes sure the object was retrieved. Security!
            if (!$content) {
                $message = sprintf('Something went wrong when retrieving content "%s" with identifier "%s".', $dataType, $identifier);
                throw new \Exception($message, 1412343097);
            }

            // Handover the localization to the Repository.
            ContentRepositoryFactory::getInstance($dataType)->localize($content, $language);

            // Get the possible error messages and store them.
            $errorMessages = ContentRepositoryFactory::getInstance()->getErrorMessages();

            // Redirect to TCEForm so that the BE User can do its job!
            if ($contentService->getNumberOfObjects() === 1) {

                if (!empty($errorMessages)) {
                    $message = sprintf('Something went wrong when localizing content "%s" with identifier "%s". <br/>%s',
                        $dataType,
                        $identifier,
                        implode('<br/>', $errorMessages)
                    );
                    throw new \Exception($message, 1412343098);
                }

                $localizedContent = $this->getLanguageService()->getLocalizedContent($content, $language);
                if (empty($localizedContent)) {
                    $message = sprintf('Oups! I could not retrieve localized content of type "%s" with identifier "%s"',
                        $content->getDataType(),
                        $content->getUid()
                    );
                    throw new \Exception($message, 1412343099);
                }

                /** @var EditUri $uri */
                $uriRenderer = GeneralUtility::makeInstance(EditUri::class);
                $uri = $uriRenderer->render($localizedContent);
                HttpUtility::redirect($uri);
                break; // no need to further continue
            }

            $result->addErrorMessages($errorMessages);
        }

        // Set the result and render the JSON view.
        $this->getJsonView()->setResult($result);
        return $this->getJsonView()->render();
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return ContentService
     */
    protected function getContentService()
    {
        return GeneralUtility::makeInstance(ContentService::class);
    }

    /**
     * @return ContentObjectResolver
     */
    protected function getContentObjectResolver()
    {
        return GeneralUtility::makeInstance(ContentObjectResolver::class);
    }

    /**
     * @return FieldPathResolver
     */
    protected function getFieldPathResolver()
    {
        return GeneralUtility::makeInstance(FieldPathResolver::class);
    }

    /**
     * Return a special view for handling JSON
     * Goal is to have this view injected but require more configuration.
     *
     * @return JsonView
     */
    protected function getJsonView()
    {
        if (!$this->view instanceof JsonView) {
            /** @var JsonView $view */
            $this->view = $this->objectManager->get(JsonView::class);
            $this->view->setResponse($this->response);
        }
        return $this->view;
    }

    /**
     * @return JsonResult|object
     */
    protected function getJsonResult()
    {
        return GeneralUtility::makeInstance(JsonResult::class);
    }

    /**
     * Signal that is called for post-processing content data send to the server for update.
     *
     * @param Content $contentObject
     * @param $fieldNameAndPath
     * @param $contentData
     * @param $counter
     * @param $savingBehavior
     * @param $language
     * @return ProcessContentDataSignalArguments
     */
    protected function emitProcessContentDataSignal(Content $contentObject, $fieldNameAndPath, $contentData, $counter, $savingBehavior, $language)
    {

        /** @var \Fab\Vidi\Signal\ProcessContentDataSignalArguments $signalArguments */
        $signalArguments = GeneralUtility::makeInstance(\Fab\Vidi\Signal\ProcessContentDataSignalArguments::class);
        $signalArguments->setContentObject($contentObject)
            ->setFieldNameAndPath($fieldNameAndPath)
            ->setContentData($contentData)
            ->setCounter($counter)
            ->setSavingBehavior($savingBehavior)
            ->setLanguage($language);

        $signalResult = $this->getSignalSlotDispatcher()->dispatch('Fab\Vidi\Controller\Backend\ContentController', 'processContentData', array($signalArguments));
        return $signalResult[0];
    }

    /**
     * Get the SignalSlot dispatcher.
     *
     * @return Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        return $this->objectManager->get(Dispatcher::class);
    }

    /**
     * Get the Clipboard service.
     *
     * @return ClipboardService
     */
    protected function getClipboardService()
    {
        return GeneralUtility::makeInstance(ClipboardService::class);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return GeneralUtility::makeInstance(LanguageService::class);
    }

    /**
     * Get the Vidi Module Loader.
     *
     * @return ModuleLoader
     */
    protected function getModuleLoader()
    {
        return GeneralUtility::makeInstance(ModuleLoader::class);
    }

}
