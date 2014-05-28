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
use TYPO3\CMS\Vidi\Persistence\MatcherObjectFactory;
use TYPO3\CMS\Vidi\Persistence\OrderObjectFactory;
use TYPO3\CMS\Vidi\Tca\TcaService;

/**
 * Controller which handles relations between content objects.
 */
class RelationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * Create relation between two contents objects.
	 *
	 * @param int $content uid
	 * @param string $dataType
	 * @param string $relationProperty
	 * @param array $relatedContents
	 * @throws \TYPO3\CMS\Vidi\Exception\MissingUidException
	 * @return string
	 */
	public function updateAction($content, $dataType, $relationProperty, array $relatedContents) {
		if ((int) $content <= 0) {
			throw new \TYPO3\CMS\Vidi\Exception\MissingUidException('Missing frontend User uid', 1351605124);
		}

		$userGroupList = '';
		if (!empty($relatedContents)) {
			$userGroupList = implode(',', $relatedContents);
		}

		$data[$dataType][$content] = array(
			$relationProperty => $userGroupList,
		);

		/** @var $tce \TYPO3\CMS\Core\DataHandling\DataHandler */
		$tce = $this->objectManager->get('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tce->start($data, array());
		$tce->process_datamap();

		return json_encode(TRUE);
	}

	/**
	 * List related content for a content object.
	 *
	 * @param int $content
	 * @param string $dataType
	 * @param string $relationProperty
	 * @param string $relatedDataType
	 * @return void
	 */
	public function listAction($content, $dataType, $relationProperty, $relatedDataType) {

		$contentRepository = ContentRepositoryFactory::getInstance($dataType);
		$content = $contentRepository->findByUid($content);
		$this->view->assign('content', $content);
		$this->view->assign('relationProperty', $relationProperty);

		$relatedContentRepository = ContentRepositoryFactory::getInstance($relatedDataType);

		// Initialize the matcher object.
		$matcher = MatcherObjectFactory::getInstance()->getMatcher(array(), $relatedDataType);
		$order = OrderObjectFactory::getInstance()->getOrder($relatedDataType);

		$relatedContents = $relatedContentRepository->findBy($matcher, $order);

		$this->view->assign('relatedContents', $relatedContents);
		$this->view->assign('relatedDataType', $relatedDataType);
		$this->view->assign('relatedContentTitle', TcaService::table($relatedDataType)->getTitle());
	}
}
