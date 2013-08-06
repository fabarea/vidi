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
 * Controller which handles actions related to Vidi.
 */
class ContentController extends \TYPO3\CMS\Vidi\Controller\BaseController {

	/**
	 * @var \TYPO3\CMS\Vidi\Domain\Repository\ContentRepository
	 * @inject
	 */
	protected $contentRepository;

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 * @inject
	 */
	protected $pageRenderer;

	/**
	 * @throws \TYPO3\CMS\Media\Exception\StorageNotOnlineException
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
		$matcherObject->setDefaultLogicalOperator(\TYPO3\CMS\Vidi\QueryElement\Query::LOGICAL_OR);
		foreach ($matches as $propertyName => $value) {
			$matcherObject->addMatch($propertyName, $value);
		}

		$orderObject = $this->createOrderObject();
		$pagerObject = $this->createPagerObject();

		// Query the repository
		$contents = $this->contentRepository->findBy($matcherObject, $orderObject, $pagerObject->getLimit(), $pagerObject->getOffset());
		$numberOfContents = $this->contentRepository->countBy($matcherObject);
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
	 * Action update media.
	 *
	 * @param array $content
	 * @return void
	 * @dontvalidate $content
	 */
	public function updateAction(array $content) {
		$this->contentRepository->updateAsset($content);
		$contentObject = $this->contentRepository->findByUid($content['uid']);
		$result['status'] = TRUE;
		$result['action'] = 'update';
		$result['object'] = array(
			'uid' => $contentObject->getUid(),
			'title' => $contentObject->getTitle(),
		);

		# Json header is not automatically respected in the BE... so send one the hard way.
		header('Content-type: application/json');
		return json_encode($result);
	}

	/**
	 * Delete a row given a media uid.
	 * This action is expected to have a parameter format = json
	 *
	 * @param int $content
	 * @return string
	 */
	public function deleteAction($content) {
		$labelField = \TYPO3\CMS\Vidi\Tca\TcaServiceFactory::getTableService()->getLabelField();
		$getter = 'get' . ucfirst($labelField);
		$contentObject = $this->contentRepository->findByUid($content);
		$result['status'] = $this->contentRepository->remove($contentObject);
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
	 * Mass delete a media
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
